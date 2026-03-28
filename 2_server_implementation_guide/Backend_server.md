# Backend Server Configuration Guide

## CityMart Online — AWS Three-Tier Architecture

This document provides the backend server setup steps for the **CityMart Online mini e-commerce application** deployed in a **three-tier AWS architecture** across **eu-west-2a** and **eu-west-2b**.

The backend servers are hosted in the **Application Tier private subnets** and are responsible for:

* Running the **PHP application**
* Serving API endpoints through **Apache**
* Connecting securely to the **database tier**
* Pulling application code from **Amazon S3**
* Operating behind an **Internal Application Load Balancer**

\---

## 1\. Installation Steps on Amazon Linux EC2 Instance

# Update the instance first:

```bash
sudo yum update -y


# Install MySQL client:
sudo dnf install mariadb105 -y


# Install AWS CLI:
sudo dnf install awscli -y


# Install Git:
sudo dnf install git -y


# Install PHP and required extensions:
sudo dnf install -y php php-cli php-common php-mysqlnd php-mbstring php-xml php-zip


# Install ca-certificates:
sudo dnf install ca-certificates -y
sudo update-ca-trust


# Install unzip:
sudo dnf install unzip -y


# Install jq:
sudo dnf install jq -y


# Install Apache:
sudo dnf install httpd -y


# Start and enable Apache:
sudo systemctl enable httpd
sudo systemctl start httpd


## 2. Quick Sanity Checks After Installation

# Verify all required packages:

mysql --version
aws --version
git --version
curl --version
php -v
apachectl -v
unzip -v
jq --version


# Check Apache service status:
sudo systemctl status httpd --no-pager


# Confirm PHP modules:
php -m | grep -E 'mysqli|pdo\_mysql|mbstring|xml|zip'



## 3. Download Application Code from S3 and Start the Application

# Download backend application files from S3:
sudo mkdir -p /var/www/html
sudo aws s3 sync s3://3-tier-architecture-sam /var/www/html


# Verify files copied successfully
ls -R /var/www/html


# Copy Apache virtual host configuration:
	- citymart.conf is inside the backend code under an apache folder, run:
sudo cp /var/www/html/apache/citymart.conf /etc/httpd/conf.d/citymart.conf


# Check if the file exists in the destination folder
ls -l /etc/httpd/conf.d/citymart.conf


# Set correct ownership and permissions:
```Bash
sudo chown -R apache:apache /var/www/html
sudo find /var/www/html -type d -exec chmod 755 {} \\;
sudo find /var/www/html -type f -exec chmod 644 {} \\;



## 4. Go to the Application folder

#Move into the backend config folder where database.php is stored.

cd /var/www/html/config


# Then confirm the file is there:

ls


We should see:
```Plain text
app.php database.php



## 5. Update Application Configuration with Database Information

# Edit the backend database configuration file:
sudo vi /var/www/html/config/database.php


# Update it with the correct database details:
 - Retrieve key connection details for RDS database instances
```Bash 
aws rds describe-db-instances \\
--region eu-west-2 \\
--query "DBInstances\[\*].\[DBInstanceIdentifier,Endpoint.Address,Endpoint.Port,MasterUsername,DBName]" \\
--output table

```php

<?php
return \[
  'host' => 'citymart-database.cdgowmasatge.eu-west-2.rds.amazonaws.com',
  'port' => '3306',
  'name' => 'citymart',
  'user' => 'admin',
  'pass' => 'phoenix80384citymart',
  'charset' => 'utf8mb4',
];


	Note: 
Inside vi:
	- press i to enter insert mode
	- make your changes
	- press Esc
	- type:
	```bash
	:wq



## 6. Connect to the Database and Perform Basic Configuration

# Use the database endpoint from your Amazon RDS or Aurora database:
mysql -h <DB\_ENDPOINT> -u <DB\_USERNAME> -p

	- fill out our details to give: 
```Bash
mysql -h citymart-database.cdgowmasatge.eu-west-2.rds.amazonaws.com -u admin -p


# After connecting, create and use the application database if required:
```SQL

CREATE DATABASE IF NOT EXISTS `citymart`;
USE `citymart`;


# Verify it exists:
```sql
SHOW DATABASES;
USE citymart;
SHOW TABLES;


# Run the schema and seed scripts after downloading the application code:

mysql -h citymart-database.cdgowmasatge.eu-west-2.rds.amazonaws.com -u admin -p citymart < /var/www/html/scripts/migrate.sql

mysql -h citymart-database.cdgowmasatge.eu-west-2.rds.amazonaws.com -u admin -p citymart < /var/www/html/scripts/seed.sql


# To verify this:

mysql -h citymart-database.cdgowmasatge.eu-west-2.rds.amazonaws.com -u admin -p -D citymart -e "SHOW TABLES;"


## 7. Restart Apache 

# Restart Apache:
sudo systemctl restart httpd


# Enable Apache on boot:
sudo systemctl enable httpd


# Check status:
sudo systemctl status httpd



## 8. Verify That the Application Is Running

# Verify Apache locally:
sudo apachectl -t \&\& sudo systemctl is-active httpd \&\& curl -I http://localhost


# Verify backend health endpoint:
curl http://localhost/api/health.php
curl -s http://localhost/api/health.php | jq .
curl -s -w "\\nHTTP:%{http\_code} TIME:%{time\_total}s\\n" http://localhost/api/health.php


# Verify product listing endpoint:
curl -i http://localhost/api/products.php
curl -fsS http://localhost/api/products.php | jq '.data.products\[] | {id,name,price}'


# Verify a specific product:
curl -i "http://localhost/api/product.php?id=1"
curl -s "http://localhost/api/product.php?id=1" | jq


# Verify database connectivity from PHP host:
mysql -h citymart-database.cdgowmasatge.eu-west-2.rds.amazonaws.com -u admin -p -D citymart -e "SELECT NOW();"


# One powerful check: 

bash -c 'echo "===== SYSTEM ====="; hostname; date; echo; echo "===== SERVICES ====="; systemctl is-active httpd php-fpm; echo; echo "===== APACHE CONFIG ====="; sudo apachectl -t; echo; echo "===== PHP VERSION ====="; php -v | head -n 1; echo; echo "===== DISK ====="; df -h /; echo; echo "===== MEMORY ====="; free -h; echo; echo "===== DATABASE CONNECTION ====="; php -r '"'"'$c=require "/var/www/html/config/database.php"; try{$pdo=new PDO("mysql:host={$c\["host"]};port={$c\["port"]};dbname={$c\["database"]};charset={$c\["charset"]}",$c\["username"],$c\["password"],\[PDO::ATTR\_ERRMODE=>PDO::ERRMODE\_EXCEPTION]); echo "DB CONNECT OK\\n"; foreach($pdo->query("SHOW TABLES") as $r){echo $r\[0]."\\n";}}catch(Throwable $e){echo "DB FAIL: ".$e->getMessage()."\\n";}'"'"'; echo; echo "===== API HEALTH ====="; curl -s http://localhost/api/health.php; echo; echo "===== API PRODUCTS ====="; curl -s http://localhost/api/products.php | head -c 400; echo; echo; echo "===== APACHE LOG (LAST 10) ====="; sudo tail -n 10 /var/log/httpd/error\_log'

sudo systemctl enable httpd
sudo systemctl enable php-fpm



## Summary

- The backend servers in the CityMart Online architecture:
- Run Apache + PHP
- Serve the application API
- Connect securely to the database tier
- Pull backend application code from Amazon S3
- Operate behind an Internal Application Load Balancer
- Support the web tier through secure internal application routing
- This design ensures security, scalability, maintainability, and alignment with three-tier architecture best practices.


