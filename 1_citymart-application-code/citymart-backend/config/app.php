<?php
/**
 * CityMart Backend - App configuration
 *
 * Scenario notes:
 * - Customers access the platform via Route 53 custom domain over HTTPS.
 * - ACM certificate is attached to the PUBLIC ALB listener (443).
 * - Web tier (Nginx) proxies /api/* to the internal ALB -> backend (Apache/PHP).
 *
 * Important:
 * - TLS terminates at the Public ALB. Backend may see HTTP internally.
 * - If you later need strict HTTPS checks, rely on X-Forwarded-Proto headers.
 */

return [
  // Basic identity
  'app_name' => getenv('APP_NAME') ?: 'CityMart Online',
  'env'      => getenv('APP_ENV') ?: 'dev',

  // Public domain customers use (Route 53 -> Public ALB)
  // Example: citymart.example.com
  'public_domain' => getenv('PUBLIC_DOMAIN') ?: '',

  // Base URL for generating links (optional; not required for API-only backends)
  // Example: https://citymart.example.com
  'public_base_url' => getenv('PUBLIC_BASE_URL') ?: '',

  // If "true", app will treat requests as secure when X-Forwarded-Proto=https
  // Set TRUE when behind ALB/Reverse proxy (recommended).
  'trust_proxy_headers' => filter_var(getenv('TRUST_PROXY_HEADERS') ?: 'true', FILTER_VALIDATE_BOOLEAN),

  // If "true", backend can enforce HTTPS at the edge (ALB) by checking forwarded proto.
  // For labs, keep false unless you implement redirect/deny logic in endpoints.
  'enforce_https' => filter_var(getenv('ENFORCE_HTTPS') ?: 'false', FILTER_VALIDATE_BOOLEAN),

  /**
   * CORS (only needed if frontend calls backend using a DIFFERENT domain)
   * Recommended design for this lab:
   * - Frontend uses same domain and calls /api/* through Nginx.
   * - Then CORS is typically NOT needed.
   *
   * If you decide to use a separate API domain later (e.g., api.citymart.example.com),
   * set ALLOWED_ORIGINS to the frontend origin(s):
   * - https://citymart.example.com
   */
  'cors' => [
    'enabled' => filter_var(getenv('CORS_ENABLED') ?: 'false', FILTER_VALIDATE_BOOLEAN),
    // Comma-separated list, e.g. "https://citymart.example.com,https://www.citymart.example.com"
    'allowed_origins' => array_values(array_filter(array_map('trim', explode(',', getenv('ALLOWED_ORIGINS') ?: '')))),
    'allowed_methods' => ['GET', 'POST', 'OPTIONS'],
    'allowed_headers' => ['Content-Type', 'Authorization'],
    'allow_credentials' => false,
    'max_age_seconds' => 600,
  ],

  // Operational settings
  'log_level' => getenv('LOG_LEVEL') ?: 'info',
];
