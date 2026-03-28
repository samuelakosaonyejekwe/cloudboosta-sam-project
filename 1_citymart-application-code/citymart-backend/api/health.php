<?php

require_once __DIR__ . '/../src/Response.php';

Response::ok([
  'status' => 'healthy',
  'service' => 'citymart-backend',
  'time' => gmdate('c'),
]);
