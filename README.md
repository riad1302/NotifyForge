# CodeIgniter 4 Bulk User Import with Redis and Firebase

This project demonstrates:

- Command line usage in CodeIgniter 4
- Bulk data insertion (12,000 users) using Faker
- Redis integration (list push)
- Firebase push notifications

---

## 📦 Prerequisites

- PHP 8.1+
- MySQL or MariaDB
- Redis Server
- Firebase Project with Cloud Messaging enabled
- Composer

---

## 🚀 Setup Instructions

1. **Clone the Repository**
   ```bash
   git clone https://github.com/riad1302/NotifyForge.git
   cd NotifyForge
   composer install
   Copy .env.example to .env
   php spark migrate
   redis-server
   php spark push:bulk



