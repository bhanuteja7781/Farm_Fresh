# 🌾 FarmFresh — Hyper-Local Farm Produce Marketplace

✨ *Connecting Local Farmers Directly with Households for Organic, Zero-Middleman Produce*

FarmFresh is a **full-stack, multi-role e-commerce web platform** designed to bridge the gap between local farmers and customers. It features hyper-local distance-based produce discovery, 6-digit delivery pin verification, Razorpay online payments & COD, a 7.5% platform revenue fee model, and dedicated role-based dashboards for Customers, Farmers, and System Administrators.

🌐 **GitHub Repository:** [https://github.com/bhanuteja7781/Farm_Fresh](https://github.com/bhanuteja7781/Farm_Fresh)  
☁️ **Deployment:** AWS EC2 Instance (`13.50.239.148`) | Docker Containerized (Nginx + PHP-FPM + MySQL)

---

## 🌟 Why FarmFresh?

Traditional agricultural supply chains rely heavily on multiple middlemen, reducing profits for farmers while inflating prices for consumers. **FarmFresh** solves this by offering:

- 📍 **Hyper-Local Produce Discovery:** Sorts and displays produce based on geographic distance from customer coordinates.
- 👨‍🌾 **Direct Farmer Earnings:** Farmers list produce directly, earning fair prices with transparent 7.5% platform service fees.
- 🔑 **6-Digit Delivery Verification:** Secure delivery confirmation system using one-time customer verification PINs.
- 💳 **Seamless Payments:** Integrated Razorpay checkout alongside Cash on Delivery (COD).
- 🛡️ **Role-Based Security:** Isolated workflows and navigation for Customers, Farmers, and Administrators.

---

## ✨ Core Features

### 🔐 Authentication & Security
- User registration and login with encrypted password storage.
- HTTP-Only JWT / Session Token verification.
- Role-based route authorization (Customer, Farmer, Admin).
- Fixed navbar & profile-based red Logout button.

### 🛒 Customer Experience
- Distance-aware farm produce listing (geolocating customer latitude/longitude).
- Interactive shopping cart with real-time stock validation limits.
- Dual checkout: **Razorpay Online Payment** or **Cash on Delivery**.
- Delivery verification code generation (6-digit PIN).
- Order history tracking & delivery status indicators.
- Produce rating and review system.

### 👨‍🌾 Farmer Dashboard
- Comprehensive metrics overview: Total Earnings, Platform Fee (7.5%), Orders, Stock.
- Product inventory management: Add, Edit, Delete produce listings with photo uploads.
- Real-time order fulfillment pipeline (Pending -> Confirmed -> Out for Delivery -> Delivered).
- Secure order completion via **6-Digit Customer Verification Code Modal**.
- Customer reviews & rating feedback monitor.

### 👨‍💼 Administrator Panel
- Unified **3×3 Key Stat Grid**:
  - Registered Customers · Registered Farmers · Gross Sales
  - Total Products · Total Orders · Pending Orders
  - Monthly Gross · Platform Revenue (7.5%) · Top Product
- Full management tables for Users, Products, and Orders with website-green styled headers (`#28a745`).

---

## 🛠️ Architecture & Tech Stack

| Layer | Technologies Used |
| :--- | :--- |
| **Frontend** | HTML5, CSS3 (Vanilla Design System), JavaScript (ES6+), FontAwesome 6 |
| **Backend API** | PHP RESTful API Micro-endpoints |
| **Database** | MySQL 8.0 (Structured Relational Schema) |
| **Containerization** | Docker, Docker Compose, Nginx, PHP-FPM |
| **Cloud Hosting** | AWS EC2 Linux Instance (`13.50.239.148`) |
| **Payments** | Razorpay Web Checkout SDK |

---

## 🚀 Docker Deployment & Setup

### Prerequisites
- [Docker & Docker Compose](https://www.docker.com/) installed on your machine or server.

### Quick Start with Docker
```bash
# 1. Clone the repository
git clone https://github.com/bhanuteja7781/Farm_Fresh.git
cd Farm_Fresh

# 2. Build and launch Docker containers
docker-compose up -d --build
```

The application services will start up:
- **Nginx Web Server:** Runs on port `80` (or configured port)
- **PHP-FPM Backend:** Internal API execution
- **MySQL Container:** Port `3306` with automatic schema initialization

---

## 💻 AWS EC2 Deployment Guide

To deploy or update on an AWS EC2 instance (e.g. `13.50.239.148`):

1. **SSH into the EC2 Instance:**
   ```bash
   ssh -i your-key.pem ubuntu@13.50.239.148
   ```

2. **Pull Latest Changes & Restart Services:**
   ```bash
   cd /path/to/Farm_Fresh
   git pull origin main
   docker-compose down
   docker-compose up -d --build
   ```

---

## 👨‍💻 Author

**Bhanu Teja**  
💡 Computer Science Engineering Student | Full Stack Developer  
🔗 **LinkedIn:** [https://www.linkedin.com/in/bhanuteja7781/](https://www.linkedin.com/in/bhanuteja7781/)  
🐙 **GitHub:** [https://github.com/bhanuteja7781](https://github.com/bhanuteja7781)

---

## 📜 License & Acknowledgments

This project is built for educational and commercial agricultural empowerment purposes. If you find this project inspiring or helpful, consider giving it a ⭐ on GitHub!

