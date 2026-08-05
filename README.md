<div align="center">

# 🌾 FarmFresh — Local Farmer Marketplace

### ✨ Connecting Local Farmers Directly with Households for Organic, Zero-Middleman Produce

[![Live AWS EC2 Demo](https://img.shields.io/badge/🌐%20Live%20EC2-13.50.239.148-28A745?style=for-the-badge&logo=amazonaws&logoColor=white)](http://13.50.239.148/)
[![GitHub Repo](https://img.shields.io/badge/🐙%20GitHub-Farm__Fresh-181717?style=for-the-badge&logo=github&logoColor=white)](https://github.com/bhanuteja7781/Farm_Fresh)
[![Docker](https://img.shields.io/badge/Docker-Containerized-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://www.docker.com/)
[![Nginx](https://img.shields.io/badge/Nginx-Web%20Server-009639?style=for-the-badge&logo=nginx&logoColor=white)](https://nginx.org/)
[![PHP](https://img.shields.io/badge/PHP-8.1%20FPM-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-8.0-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![Razorpay](https://img.shields.io/badge/Payments-Razorpay-008CDD?style=for-the-badge&logo=razorpay&logoColor=white)](https://razorpay.com/)

</div>

---

## 📖 Overview

**FarmFresh** is a full-stack, production-ready hyper-local e-commerce marketplace engineered to eliminate agricultural intermediaries by directly connecting local farmers with household consumers.

The platform provides a **distance-aware produce discovery engine**, secure **6-digit delivery PIN verification**, dual payment options (**Razorpay Checkout & Cash on Delivery**), a transparent **7.5% platform revenue fee**, and dedicated role-based dashboards for **Customers**, **Farmers**, and **Administrators**.

> **🌐 Live EC2 Instance:** [http://13.50.239.148/](http://13.50.239.148/)
> **🐙 GitHub Repository:** [https://github.com/bhanuteja7781/Farm_Fresh](https://github.com/bhanuteja7781/Farm_Fresh)

---

## ⚡ System Architecture

```text
               ┌─────────────────────────────────────────┐
               │          Customer / Farmer Browser      │
               └────────────────────┬────────────────────┘
                                    │
                                    ▼
               ┌─────────────────────────────────────────┐
               │         Nginx Reverse Proxy Container   │
               │         http://13.50.239.148            │
               └─────────┬─────────────────────┬─────────┘
                         │                     │
        ┌────────────────┘                     └────────────────┐
        ▼ (Static Frontend / HTML / JS)                         ▼ (REST API / /api/*)
 ┌───────────────┐                                     ┌───────────────┐
 │ Static Assets │                                     │  PHP 8.1 FPM  │
 │ Nginx HTML    │                                     │ Container     │
 └───────────────┘                                     └───────┬───────┘
                                                               │
                                                               ▼
                                                       ┌───────────────┐
                                                       │  MySQL 8.0 DB │
                                                       │ Container     │
                                                       └───────────────┘
```

---

## ✨ Core Features & Platform Capabilities

### 🛒 Customer Experience
- 📍 **Hyper-Local Produce Discovery**: Automatically calculates distance using customer geolocation coordinates (`latitude`/`longitude`) and sorts produce from nearest local farms.
- 🛍️ **Interactive Shopping Cart**: Live cart state management with real-time stock availability validation limits.
- 💳 **Flexible Payment Gateway**: Complete purchases via **Razorpay Online Payment SDK** or **Cash on Delivery (COD)**.
- 🔑 **Delivery PIN Verification**: Auto-generates a unique 6-digit verification code per order to guarantee safe handoff.
- 📦 **Order Tracking & Feedback**: Real-time order fulfillment status badges and produce quality star rating system.

### 👨‍🌾 Farmer Dashboard
- 📊 **Financial & Earnings Overview**: Displays Total Revenue, Platform Fee (7.5%), Active Stock, and Order Volume.
- 🚜 **Product Inventory Portal**: Add, Edit, and Delete farm produce listings with image upload capability.
- 🚚 **Fulfillment Pipeline**: Track and manage order progression (`Pending` ➔ `Confirmed` ➔ `Out for Delivery` ➔ `Delivered`).
- 🔐 **Secure Handover Verification**: Integrated **6-Digit Customer Verification Code Modal** preventing fraudulent delivery claims.
- ⭐ **Produce Reviews Monitor**: Track customer feedback and rating metrics for listed crops.

### 👨‍💼 Administrator Panel & Governance
- 📈 **Unified 3×3 Key Stat Grid**:
  - `Registered Customers` · `Registered Farmers` · `Gross Sales`
  - `Total Products` · `Total Orders` · `Pending Orders`
  - `Monthly Gross` · `Platform Revenue (7.5%)` · `Top Product`
- 👥 **User & Merchant Management**: Search, monitor, and manage customer and farmer accounts.
- 📦 **Global Inventory Audit**: Review active produce listings across all registered farmers.
- 📑 **Order Inspection & Audit Trail**: Full transaction logs with website-green header styling (`#28a745`).

---

## 🛠️ Tech Stack

| Domain | Technologies |
|---|---|
| **Frontend** | HTML5, Vanilla CSS3 (Custom Design System), JavaScript (ES6+), FontAwesome 6 |
| **Backend** | PHP 8.1 FPM (Modular RESTful Micro-APIs) |
| **Database** | MySQL 8.0 (Relational Schema & Foreign Keys) |
| **Containerization** | Docker, Docker Compose, Nginx |
| **Cloud Infrastructure**| AWS EC2 Ubuntu Linux (`13.50.239.148`) |
| **Authentication** | Token Cookie Validation & Role-Based Middleware |
| **Payment Gateway** | Razorpay Web Checkout SDK |

---

## 📂 Project Structure

```text
Farm_Fresh/
├── backend/                         # PHP REST API Engine
│   ├── api/                         # Endpoints grouped by domain
│   │   ├── auth/                    # Login, Register, Profile APIs
│   │   ├── cart/                    # Add, Update, View, Remove Cart APIs
│   │   ├── dashboard/               # Admin Summary (3x3 grid) & Farmer Stats APIs
│   │   ├── delivery/                # Delivery Scheduling APIs
│   │   ├── orders/                  # Place, MyOrders, Status & Verification APIs
│   │   ├── payment/                 # Razorpay Order Creation API
│   │   ├── products/                # Search, Add, Edit, Delete, Farmer Products APIs
│   │   ├── reviews/                 # Product Review & Rating APIs
│   │   └── user/                    # Current User Context API
│   ├── config/                      # Database Connection (Docker/XAMPP Auto-Detect)
│   └── uploads/                     # Product Images CDN Storage
│
├── docker/                          # Nginx Web Server Configuration
│   └── nginx/                       # Default.conf with clean URI rewrites
│
├── frontend/                        # Frontend Application
│   └── public/                      # Static pages & assets
│       ├── admin/                   # Admin Panel Pages (Dashboard, Users, Products, Orders)
│       ├── auth/                    # Login & Register Pages
│       ├── farmer/                  # Farmer Pages (Dashboard, Add/Edit Product, Orders, Reviews)
│       ├── index.html               # Main Hyper-Local Marketplace Landing Page
│       ├── cart.html                # Shopping Cart Page
│       ├── myorders.html            # Customer Order History Page
│       ├── placeorder.html          # Checkout & Razorpay Payment Page
│       └── profile.html             # Profile Management & Red Logout Button
│
├── .dockerignore                    # Docker build exclusion rules
├── .env.example                     # Environment template
├── .gitignore                       # Git ignore configuration
├── database.sql                     # Complete MySQL Schema & Sample Data
├── Dockerfile.php                   # PHP 8.1 FPM Container Definition
├── docker-compose.yml               # Multi-container orchestration
└── README.md                        # Master Documentation
```

---

## 🛠️ Local Development Setup

### 1. Clone the repository
```bash
git clone https://github.com/bhanuteja7781/Farm_Fresh.git
cd Farm_Fresh
```

### 2. Launch with Docker Compose
```bash
docker-compose up -d --build
```

The application stack will automatically spin up:
- **Web Interface**: `http://localhost`
- **MySQL Database**: `localhost:3306` (`user: root`, `password: root`, `database: farmfresh`)

### 3. Verify Container Status
```bash
docker ps
```

---

## 🚀 Production Deployment Guide (AWS EC2)

### Deploy on AWS EC2 Instance (`13.50.239.148`)
1. **SSH into the EC2 Server:**
   ```bash
   ssh -i /path/to/your-key.pem ubuntu@13.50.239.148
   ```

2. **Pull Latest Source & Rebuild Containers:**
   ```bash
   cd /var/www/Farm_Fresh
   git pull origin main
   docker-compose down
   docker-compose up -d --build
   ```

---

## 🔒 Security & Data Integrity

- **Role Guard Authorization**: Isolated page access for Customer, Farmer, and Admin routes.
- **Delivery Fraud Prevention**: Required 6-digit OTP verification code before order completion.
- **SQL Injection Safeguards**: Prepared statements across all PHP database queries.
- **Stock Limit Guarding**: Server-side and client-side stock validation prevents overselling.
- **Session Protection**: Encrypted auth token cookie validation.

---

## 👨‍💻 Author

**Bhanu Teja**
💡 Computer Science Engineering Student | Full Stack Developer
🔗 **LinkedIn:** [https://www.linkedin.com/in/bhanuteja7781/](https://www.linkedin.com/in/bhanuteja7781/)
🐙 **GitHub:** [https://github.com/bhanuteja7781](https://github.com/bhanuteja7781)

---

## 📄 License

This project is built for educational and commercial agricultural empowerment purposes © [Bhanu Teja](https://github.com/bhanuteja7781).

<div align="center">

⭐ **Star this repository if you find FarmFresh useful!** ⭐

</div>
