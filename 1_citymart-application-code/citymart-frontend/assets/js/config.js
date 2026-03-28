/**
 * CityMart Frontend Config
 *
 * Scenario:
 * - Route 53 hosted zone + ACM certificate attached to the PUBLIC ALB
 * - Customers access the site securely via: https://<YOUR_CUSTOM_DOMAIN>
 * - Nginx on the web tier reverse-proxies /* to the INTERNAL ALB (backend)
 *
 * Recommended pattern:
 * - Use SAME-ORIGIN API calls to avoid CORS headaches:
 *     API_BASE_URL = "/api"
 *
 * If you ever decide to use a separate API hostname like:
 *     https://api.<YOUR_CUSTOM_DOMAIN>
 * then set API_BASE_URL to that full URL instead.
 */

(function (global) {
  const CONFIG = {
    // Same-origin API path. Works with HTTPS automatically.
    API_BASE_URL: "",

    // Request timeout (ms)
    TIMEOUT_MS: 12000,

    // Basic environment label (optional)
    ENV: "prod"
  };

  // Expose to browser scripts
  global.CITYMART_CONFIG = CONFIG;
})(window);
