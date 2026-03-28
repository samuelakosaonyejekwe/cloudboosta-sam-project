<?php
/**
 * CityMart Backend - Response helper
 *
 * Notes for our 3-tier design:
 * - Customers access the app via HTTPS on the PUBLIC ALB (Route 53 + ACM).
 * - Web tier (Nginx) typically proxies /api/* to the internal ALB over HTTP.
 * - So this backend mostly returns JSON to Nginx/internal callers.
 */

class Response
{
    /**
     * Send a JSON response and exit.
     */
    public static function json(array $payload, int $status = 200, array $headers = []): void
    {
        // Status + content type
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');

        // Security / operational headers (safe defaults for JSON APIs)
        header('X-Content-Type-Options: nosniff');
        header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
        header('Pragma: no-cache');

        // Correlation: propagate request id if present (helps tracing through ALB/Nginx)
        $requestId = self::getRequestId();
        if ($requestId !== null) {
            header('X-Request-Id: ' . $requestId);
        }

        // Extra custom headers
        foreach ($headers as $k => $v) {
            if (is_string($k) && $k !== '') {
                header($k . ': ' . $v);
            }
        }

        // Encode JSON safely
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        // If encoding fails, return a controlled error response
        if ($json === false) {
            http_response_code(500);
            $fallback = [
                'ok' => false,
                'error' => [
                    'code' => 'JSON_ENCODING_FAILED',
                    'message' => 'Server failed to encode response as JSON',
                ],
            ];
            echo json_encode($fallback);
            exit;
        }

        echo $json;
        exit;
    }

    /**
     * Standard success response.
     */
    public static function ok(array $data = [], int $status = 200): void
    {
        $payload = [
            'ok' => true,
            'data' => $data,
            'meta' => [
                'time' => gmdate('c'),
            ],
        ];

        $rid = self::getRequestId();
        if ($rid !== null) {
            $payload['meta']['request_id'] = $rid;
        }

        self::json($payload, $status);
    }

    /**
     * Standard error response.
     * - $status: HTTP status code (400, 401, 404, 409, 422, 500, etc.)
     * - $code: short machine-readable error code
     * - $details: optional extra context (avoid leaking secrets)
     */
    public static function error(
        string $message,
        int $status = 400,
        string $code = 'BAD_REQUEST',
        array $details = []
    ): void {
        $payload = [
            'ok' => false,
            'error' => [
                'code' => $code,
                'message' => $message,
                'details' => $details,
            ],
            'meta' => [
                'time' => gmdate('c'),
            ],
        ];

        $rid = self::getRequestId();
        if ($rid !== null) {
            $payload['meta']['request_id'] = $rid;
        }

        self::json($payload, $status);
    }

    /**
     * Convenience: 405 Method Not Allowed
     */
    public static function methodNotAllowed(array $allowed = ['GET']): void
    {
        header('Allow: ' . implode(', ', $allowed));
        self::error('Method not allowed', 405, 'METHOD_NOT_ALLOWED', ['allowed' => $allowed]);
    }

    /**
     * Extract a request id from common proxy headers if present.
     */
    private static function getRequestId(): ?string
    {
        $candidates = [
            'HTTP_X_REQUEST_ID',
            'HTTP_X_AMZN_TRACE_ID',     // ALB trace header
            'HTTP_X_CORRELATION_ID',
        ];

        foreach ($candidates as $key) {
            if (!empty($_SERVER[$key]) && is_string($_SERVER[$key])) {
                $val = trim($_SERVER[$key]);
                if ($val !== '') return $val;
            }
        }
        return null;
    }
}
