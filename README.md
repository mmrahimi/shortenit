# ShortenIt

A Laravel API for shortening URLs.

## 📦 Features
- Support for guest requests with cookie management
- Collision-safe short-code generation with configurable length and retry limits
- Caching functionality through Redis
- Race-condition handling and database integrity
- Rate limiting
- CI pipeline

## 🚀 Setup
- Install PHP dependencies with `composer install`
- Configure `.env` using `.env.example` and generate an application key
- Start Redis with `docker compose up -d redis`
- Configure your MySQL database and run `php artisan migrate`
- Run the app with `php artisan serve`

## 📝 What I Learned
Practiced system-level decision making with tradeoffs, handling concurrency, maintaining data integrity, observability and having a clear agenting workflow for fast and ideal development.
