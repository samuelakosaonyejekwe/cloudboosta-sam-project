# AWS Three-Tier Architecture Design – CityMart Online

## Overview

CityMart Online is a mini e-commerce product catalog application designed to demonstrate the implementation of a classic AWS Three-Tier Architecture. The application allows customers to browse products, view product details, and submit order requests for pickup. The system is designed with scalability, security, and high availability in mind.

This architecture separates the application into three logical tiers:

1. **Frontend Tier (Presentation Layer)**
2. **Backend Tier (Application Layer)**
3. **Database Tier (Data Layer)**

## The infrastructure is deployed in the **AWS London Region (eu-west-2)** and spans **2 Availability Zones (eu-west-2a and eu-west-2b)** to provide high availability and fault tolerance.

# Architecture Overview

The CityMart architecture implements a secure and scalable web application infrastructure that separates concerns across different layers. Each layer performs a dedicated function and communicates with other layers through controlled channels.

Customer traffic flows through the architecture as follows:

* Customer → Route 53 → Public Application Load Balancer → Web Servers → Internal Application Load Balancer → Application Servers → Database
* This layered approach improves security, scalability, maintainability, and operational reliability.

\---

\---

Our Tier Architecture

IPv4 CIDR block is 10.0.0.0/16

Tier	                Subnet Name	         CIDR Block	  Availability Zone	 Purpose / Hosted Resources
Public Web Tier	        Public Web Subnet A	 10.0.0.0/20	  eu-west-2a	         Hosts Public Application Load Balancer and NAT Gateway
Public Web Tier	        Public Web Subnet B	 10.0.16.0/20	  eu-west-2b	         Hosts Public Application Load Balancer and NAT Gateway
Private Web Tier	Private Web Subnet A	 10.0.48.0/20	  eu-west-2a	         Hosts Frontend Web Servers (Nginx)
Private Web Tier	Private Web Subnet B	 10.0.64.0/20	  eu-west-2b	         Hosts Frontend Web Servers (Nginx)
Application Tier	Private App Subnet A	 10.0.96.0/20	  eu-west-2a	         Hosts Backend Application Servers (Apache + PHP)
Application Tier	Private App Subnet B	 10.0.112.0/20	  eu-west-2b	         Hosts Backend Application Servers (Apache + PHP)
Database Tier	        Private DB Subnet A	 10.0.144.0/20	  eu-west-2a	         Hosts Aurora MySQL / Amazon RDS database instance
Database Tier	        Private DB Subnet B	 10.0.160.0/20	  eu-west-2b	         Hosts Aurora MySQL / Amazon RDS database instance

\---

Key Design Characteristics
Design Principle	Implementation in the Architecture
High Availability	All tiers deployed across two Availability Zones
Network Isolation	Separate subnets for Web, Application, and Database tiers
Security	        Private subnets prevent direct internet access to internal servers
Scalability	        Load balancers and Auto Scaling Groups distribute traffic and scale instances
Fault Tolerance	        Redundant infrastructure across Availability Zones

\---

Traffic Flow Through the Architecture
Step	Component	Function
1	Route 53	Resolves domain name to the Public ALB
2	Public Application Load Balancer	Receives HTTPS traffic from users
3	Web Servers (Nginx)	Serves frontend content and forwards API requests
4	Internal Application Load Balancer	Routes requests to backend application servers
5	Backend Application Servers	Executes business logic and interacts with database
6	Database Layer	Stores and retrieves persistent application data

\---

\---

# DNS and TLS Configuration

To provide secure customer access:

* **Amazon Route 53** hosts the public DNS zone for the domain.
* **AWS Certificate Manager (ACM)** issues an SSL/TLS certificate for HTTPS.
* The certificate is attached to the **Public Application Load Balancer**.
* A **Route 53 Alias Record** directs traffic from the domain to the load balancer.

This configuration ensures encrypted HTTPS communication for users accessing the application.

\---

# Application Structure

The application code is organized into a structured repository stored in an S3 bucket for deployment to EC2 instances.



# Application Code Repository Structure for the CityMart Online Three-Tier Architecture

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
├── css/
│   └── styles.css
│
├── js/
│   ├── config.js
│   ├── api.js
│   ├── products.js
│   ├── product.js
│   └── checkout.js
│
└── img/
├── logo.png
├── rice.png
├── oil.png
├── soap.png
├── detergent.png
├── product-default.png
└── placeholder-product.png



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
├── CustomersService.php
├── Database.php
├── Db.php
├── OrdersService.php
├── ProductsService.php
├── Response.php
└── Validator.php



\---

# Frontend Tier (Presentation Layer)

The frontend layer delivers the user interface and static content to customers. It is hosted on **Nginx web servers running on EC2 instances** within private web subnets.

Responsibilities:

* Serve static HTML pages.
* Deliver CSS styling and JavaScript logic.
* Handle user interaction and navigation.
* Forward API requests to the backend.

Key components include:

* HTML pages such as `index.html`, `products.html`, and `checkout.html`.
* CSS styling (`styles.css`).
* JavaScript logic for calling backend APIs.

Nginx also acts as a **reverse proxy**, forwarding requests beginning with `/api` to the backend application servers.

\---

# Backend Tier (Application Layer)

The backend layer contains the business logic of the application and runs on **Apache + PHP EC2 instances** behind an internal load balancer.

This tier processes API requests such as:

GET /products  
GET /product?id=1  
POST /order

The backend performs:

* Business logic processing
* Data validation
* Database communication
* API response formatting

Important backend components include:

* API endpoints (`products.php`, `product.php`, `order.php`)
* Application configuration files
* Service classes handling business logic
* Database abstraction classes

The backend servers are placed behind an **internal Application Load Balancer**, ensuring they remain inaccessible from the public internet.

\---

# Database Tier (Data Layer)

The database tier stores persistent application data.

The database contains tables including:

* products
* customers
* orders

The database is deployed using **Amazon Aurora MySQL or Amazon RDS Multi-AZ**, ensuring high availability and automatic failover.

This layer resides within **private database subnets**, making it inaccessible from external networks and reachable only by application servers.

\---

# Infrastructure Deployment Steps

## 1\. Network Foundation

* Create the VPC.
* Create subnets across two availability zones.
* Create public, web private, application private, and database private subnets.

## 2\. Routing Configuration

* Create route tables.
* Attach Internet Gateway.
* Deploy NAT Gateways.
* Configure routing between subnets.

## 3\. Security Configuration

Create security groups for:

* Frontend Load Balancer
* Web Servers
* Backend Load Balancer
* Application Servers
* Database Servers

## 4\. Database Layer

* Create a database subnet group.
* Deploy the Aurora or RDS database instance.

## 5\. Load Balancers

* Deploy the public Application Load Balancer.
* Deploy the internal Application Load Balancer.
* Configure target groups.

## 6\. Server Image Preparation

Frontend AMI:

* Nginx
* Git

Backend AMI:

* Apache
* PHP
* MySQL Client
* Git

## 7\. Compute Scaling Configuration

* Create launch templates.
* Configure Auto Scaling Groups for frontend and backend servers.

\---

# Why Application Code Is Stored in S3

The application code is stored in an **Amazon S3 bucket** because:

* EC2 instances may be temporary.
* Auto Scaling may launch new instances anytime.
* New instances automatically download the latest code during startup.

This ensures consistent deployments across the environment. 

\---

# High Availability Design

The architecture ensures resilience through:

* Multi-AZ deployments
* Load balancers distributing traffic
* Redundant NAT gateways
* Auto Scaling Groups
* Database high availability

This design eliminates single points of failure.

\---

# Acknowledgement

This sample application was created from scratch to demonstrate AWS three-tier architecture principles.

# Author

Samuel Akosa Onyejekwe / Group A Cloudboosta Project Year 2026

