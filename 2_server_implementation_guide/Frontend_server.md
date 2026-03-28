# Frontend Server Configuration Guide
## CityMart Online — AWS Three-Tier Architecture (Web Tier)

This guide covers the setup of **Frontend (Web Tier) EC2 instances** for the **CityMart Online** mini e-commerce application.
Frontend servers are deployed in the **Web Tier private subnets** and perform two core functions:
- Serve the **static frontend website** (HTML/CSS/JS) using **Nginx**
- Reverse proxy `/api/*` requests to the **Internal ALB**, which forwards traffic to the **Backend (App Tier) servers**

**Customer access path (secure HTTPS):**
Route 53 (custom domain) → Public ALB (ACM HTTPS 443) → Frontend EC2 (Nginx) → Internal ALB → Backend EC2 (Apache/PHP) → Aurora/MySQL

---

## 1) Installation Steps on Amazon Linux EC2 Instance

Update OS packages:

```bash
sudo dnf update -y


# Install required packages (Nginx, Git, AWS CLI, unzip, ca-certificates, jq, wget):
sudo dnf install -y nginx git awscli unzip ca-certificates jq wget


# Enable and start Nginx:
sudo systemctl enable nginx
sudo systemctl start nginx


# Update CA trust store:
sudo update-ca-trust


## 2) Quick Sanity Checks After Installation

# Verify versions:

nginx -v
git --version
aws --version
curl --version
unzip -v
jq --version
wget --version


# Confirm Nginx is running:
sudo systemctl status nginx --no-pager


# Confirm port 80 is listening:
sudo ss -tulpn | grep ':80' || sudo netstat -tulpn | grep ':80'



## 3) Replace the server Block in /etc/nginx/nginx.conf with our nginx.conf

#Confirm where the Nginx config is located on the frontend server

- # Run:
cd /etc/nginx
ls


We should see nginx.conf and other Nginx files/folders.


# - Replace the Nginx configuration using the project config stored in S3

- (Recommended approach for Auto Scaling and repeatability)

sudo aws s3 sync s3://3-tier-architecture-sam/citymart-frontend /var/www/html

# ---------------------------------
# Set Correct Permissions
# ---------------------------------
sudo chown -R nginx:nginx /var/www/html
sudo find /var/www/html -type d -exec chmod 755 {} \;
sudo find /var/www/html -type f -exec chmod 644 {} \;


# We can manually edit if required:
sudo vi /etc/nginx/site-citymart.conf

Then replace the existing server { ... } block with the server { ... } block from our project nginx.conf.


## 4) Update Nginx Configuration (Proxy /api to the Internal ALB)

- Our web tier must forward backend requests to the Internal ALB.

- Important: Use the Internal ALB DNS name, not an IP address (ALB IPs can change).

Our internal ALB DNS name:
```Plain text


internal-citymart-backend-alb-664286771.eu-west-2.elb.amazonaws.com


# Our site-citymart.conf includes a block similar to:
```Nginx

location /api/ {
    proxy_pass http://internal-citymart-alb-123456789.eu-west-2.elb.amazonaws.com/;
    proxy_set_header Host $host;
    proxy_set_header X-Real-IP $remote_addr;
    proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
    proxy_set_header X-Forwarded-Proto $scheme;
}



# Test the Nginx configuration:
sudo nginx -t


# Reload/restart Nginx:
sudo systemctl restart nginx



## 5) Enable Secure HTTPS Access Using Route 53 + ACM

# 5.1 Request an ACM Certificate (HTTPS)

- In AWS Certificate Manager (ACM), request a public certificate for:

	citymart.samuelonyejekwe.com 

	
Choose DNS validation (recommended).



# 5.2 Create DNS Records in Route 53 (Hosted Zone)

In our Route 53 hosted zone, add:

	- ACM validation CNAME record(s) created by ACM (for certificate validation)

	- A (Alias) record pointing your custom domain to the Public ALB


Recommended DNS setup:

citymart.samuelonyejekwe.com → A (Alias) → Public ALB

www.citymart.samuelonyejekwe.com → CNAME → citymart.samuelonyejekwe.com 



# 5.3 Attach the ACM Certificate to the Public ALB

On the Public ALB:

	- Add/Update listener 443 (HTTPS) and attach the ACM certificate

	- Add listener 80 (HTTP) redirecting to 443 (HTTPS) (recommended)


# 5.4 Verify Secure Access

# - From laptop or browser:
curl -I https://citymart.samuelonyejekwe.com


# - Verify API routing through the same domain (recommended pattern):
curl -i https://citymart.samuelonyejekwe.com/api/health
curl -i https://citymart.samuelonyejekwe.com/api/products



Notes and Best Practices

- Frontend EC2 instances are in private subnets (not directly internet accessible).

- Public traffic is terminated securely at the Public ALB using ACM.

- Nginx forwards /api/* to the Internal ALB, which load balances to backend servers.

- Keep frontend servers stateless: always pull code/config from S3 during boot.



Placeholders to Replace

	<S3_BUCKET_NAME>: S3 bucket holding application code: arn:aws:s3:::3-tier-architecture-sam

	<CUSTOM_DOMAIN>: our Route 53 domain (in our project, citymart.samuelonyejekwe.com)

	Internal ALB DNS: internal-citymart-backend-alb-664286771.eu-west-2.elb.amazonaws.com


Note:
	The Nginx files and folders were not uploaded to S3 because **`/etc/nginx` contains system configuration files created automatically when Nginx is installed by the OS package manager**. In an Auto Scaling architecture, new EC2 instances should **install Nginx during startup using the user data script**, which recreates these system directories automatically. Only **application-specific files**, such as the frontend website files and any custom Nginx configuration, need to be stored in S3 and downloaded when an instance launches. Uploading the entire `/etc/nginx` folder is poor practice because system configurations can vary across OS versions and may cause compatibility or security issues. Your setup follows the **immutable infrastructure principle**, where each new instance installs software, retrieves application code from S3, and starts services automatically instead of cloning an existing server.

