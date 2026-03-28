<?php
// CityMart Backend - minimal front controller
// In a simple lab, you can call the API endpoints directly under /api/*
// This file just gives a friendly response for / (optional).

header('Content-Type: application/json');

echo json_encode([
  'service' => 'citymart-backend',
  'message' => 'Backend is running. Try /api/health, /api/products, /api/product?id=1',
  'time'    => gmdate('c')
]);
