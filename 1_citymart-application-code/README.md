# CityMart Online — Mini E-Commerce Product Catalog (3-Tier AWS + Route 53 + ACM + HTTPS)

## Overview

CityMart Online is a mini e-commerce web application designed for a **three-tier AWS architecture** that allows customers to:

* Browse products
* View product details
* Submit an order request (store pickup / offline confirmation)

This lab demonstrates how **application code** integrates with AWS infrastructure, including **Route 53 hosted zones**, **ACM TLS certificates**, and **secure HTTPS access** via a **Public Application Load Balancer (ALB)**.

\---

## Architecture Overview

### Customer Request Flow (HTTPS)

Customer Browser  
↓  
**Route 53 Custom Domain (DNS)**  
↓  
**Public ALB (HTTPS 443 with ACM certificate)**  
↓  
Web Tier (Nginx Frontend Servers in private subnets)  
↓  
Internal ALB (private)  
↓  
Application Tier (Apache + PHP API Servers in private subnets)  
↓  
Database Tier (Aurora MySQL / RDS MySQL in private subnets)

\---

## Route 53 + ACM + HTTPS (Public Access)

### 1\) Route 53 Hosted Zone

Create or use a hosted zone for your domain (example):

* `citymart.example.com`

Create a DNS record to point your domain to the **Public ALB**:

* Record type: **A (Alias)** (and optionally AAAA)
* Name: `citymart.example.com`
* Target: **Public ALB**

### 2\) ACM Certificate (TLS)

Request an **ACM public certificate** for:

* `citymart.example.com`
* (optional) `www.citymart.example.com`

Validate using **DNS validation in Route 53**.

### 3\) Attach Certificate to Public ALB

Configure the Public ALB listeners:

* **443/HTTPS** → attach ACM certificate
* **80/HTTP** → redirect to **443/HTTPS** (recommended)

### 4\) Verify HTTPS

Open in browser:

* `https://citymart.example.com`

Expected:

* A valid TLS certificate
* Secure lock icon (HTTPS)

\---

## Project Directory Structure

The `citymart-frontend/` code directory contains:

citymart-frontend/

│

├── index.html

├── products.html

├── product.html

├── checkout.html

├── order-success.html

│

└── assets/

&#x20;   ├── css/

&#x20;   │   └── styles.css

&#x20;   │

&#x20;   ├── js/

&#x20;   │   ├── config.js

&#x20;   │   ├── api.js

&#x20;   │   ├── products.js

&#x20;   │   ├── product.js

&#x20;   │   └── checkout.js

&#x20;   │

&#x20;   └── img/

&#x20;       ├── logo.png

&#x20;       ├── rice.png

&#x20;       ├── oil.png

&#x20;       ├── soap.png

&#x20;       ├── detergent.png

&#x20;       ├── product-default.png

&#x20;       └── placeholder-product.png







The `citymart-backend/` code directory contains:

citymart-backend

│

├── apache

│   └── citymart.conf

│

├── api

│   ├── health.php

│   ├── order.php

│   ├── product.php

│   └── products.php

│

├── config

│   ├── app.php

│   └── database.php

│

├── public

│   └── index.php

│

├── scripts

│   ├── migrate.sql

│   ├── seed.sql

│   └── reset\_db.sh

│

└── src

&#x20;   ├── CustomersService.php

&#x20;   ├── Database.php

&#x20;   ├── Db.php

&#x20;   ├── OrdersService.php

&#x20;   ├── ProductsService.php

&#x20;   ├── Response.php

&#x20;   └── Validator.php





\---

## Frontend (Web Tier)

The frontend is a static website served by **Nginx**.

Pages included:

|Page|Purpose|
|-|-|
|`index.html`|Home page|
|`products.html`|Displays product catalog|
|`product.html`|Displays individual product details|
|`checkout.html`|Allows customer to submit an order request|
|`order-success.html`|Displays order confirmation|

### Frontend → Backend API Calls

Recommended (best for HTTPS + custom domain):

* Frontend calls API using **relative paths**:

  * `/api/products`
  * `/api/product?id=1`
  * `/api/order`

This ensures API calls work automatically via:

* `https://<CUSTOM\\\\\\\\\\\\\\\_DOMAIN>/api/...`

\---

## Backend (Application Tier)

The backend runs on **Apache + PHP** and exposes API endpoints consumed by the frontend.

API endpoints:

|Endpoint|Method|Description|
|-|-:|-|
|`/api/health`|GET|Health check for ALB target group|
|`/api/products`|GET|Returns product catalog|
|`/api/product?id=1`|GET|Returns product details|
|`/api/order`|POST|Creates a new order request|

Backend services handle:

* Database communication
* Input validation
* Product retrieval
* Order creation

\---

## Database Schema

The database stores:

* `products` — catalog items
* `customers` — customer contact info
* `orders` and `order\\\\\\\\\\\\\\\_items` — submitted orders and line items

Initialization scripts are in:

* `backend/scripts/migrate.sql`
* `backend/scripts/seed.sql`

\---

## Nginx Configuration (Web Tier)

Nginx serves the frontend and reverse-proxies API requests to the application tier.

Files:

* `nginx/nginx.conf` — base configuration
* `nginx/site-citymart.conf` — site config + `/api` reverse proxy

Typical intent:

* `/` → serve static frontend files
* `/api/\\\\\\\\\\\\\\\*` → proxy to the **Internal ALB** (app tier)

\---

## Environment Configuration (Backend DB)

Database configuration is in:

* `backend/config/database.php`

Set these (typically via userdata / launch template environment variables):

* `DB\\\\\\\\\\\\\\\_HOST`
* `DB\\\\\\\\\\\\\\\_NAME`
* `DB\\\\\\\\\\\\\\\_USER`
* `DB\\\\\\\\\\\\\\\_PASS` (or `DB\\\\\\\\\\\\\\\_PASSWORD` depending on your implementation)

\---

## Testing the Application

### 1\) Verify HTTPS (Public)

Open:

* `https://<CUSTOM\\\\\\\\\\\\\\\_DOMAIN>`

### 2\) Verify API via the same domain

Run:

```bash
curl -i https://<CUSTOM\\\\\\\\\\\\\\\_DOMAIN>/api/health
curl -i https://<CUSTOM\\\\\\\\\\\\\\\_DOMAIN>/api/products
curl -i "https://<CUSTOM\\\\\\\\\\\\\\\_DOMAIN>/api/product?id=1"


Create an order (example)

curl -i -X POST https://<CUSTOM\\\\\\\\\\\\\\\_DOMAIN>/api/order \\\\\\\\\\\\\\\\
  -H "Content-Type: application/json" \\\\\\\\\\\\\\\\
  -d '{
    "customer\\\\\\\\\\\\\\\_name": "Samuel",
    "customer\\\\\\\\\\\\\\\_email": "samuel@example.com",
    "customer\\\\\\\\\\\\\\\_phone": "+35700000000",
    "items": \\\\\\\\\\\\\\\[
      {"product\\\\\\\\\\\\\\\_id": 1, "qty": 2},
      {"product\\\\\\\\\\\\\\\_id": 2, "qty": 1}
    ]
  }'


Purpose of This Project

- This lab demonstrates:

- Three-tier cloud architecture (Web / App / DB)

- Secure public access via Route 53 + ACM + HTTPS

- Separation of frontend and backend services

- Integration with a relational database

- Load balancing and scalable design using ALBs and private subnets


Author

Samuel Akosa Onyejekwe — Cloud Engineering Lab Project @ Cloudboosta





