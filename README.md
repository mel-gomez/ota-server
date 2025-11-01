OTA Server (Laravel 12)

A backend API service built with Laravel 12 and MySQL, powering the OTA Client (Next.js) application.
It provides secure REST endpoints, authentication, and database logic for managing users, OTA operations, and configuration data.

Tech Stack
Component Technology Reason for Use
Framework Laravel 12 Chosen for its robust ecosystem, expressive ORM (Eloquent), and support for clean service-based architecture
Database MySQL Reliable relational database with mature Laravel support
Hosting Railway Simplifies CI/CD and database deployment with environment variable management
Authentication Laravel Sanctum / Passport Provides API token-based auth for SPA and mobile use cases

System Flow

Client Request → The Next.js client calls Laravel endpoints (e.g., /api/job-posts/\*) via Axios or Fetch.

Controller → Requests are routed to specific controllers for validation and processing.

Service Layer → Business logic is isolated in service classes for maintainability.

Model / Eloquent ORM → Queries MySQL using Eloquent relationships and scopes.

Response → JSON data returned to the client following REST standards.

Key Features

Reusable Service Classes for business logic

Integrated error and response handlers

Secure environment separation via .env files on Railway

Design Decisions (Why I Used This, Not That)

Laravel 12 vs Node/Express: Laravel provides faster scaffolding and a richer ecosystem for structured enterprise APIs. Eloquent ORM also speeds up development with relationships and mutators.

MySQL vs NoSQL (MongoDB): Chosen for strong relational data integrity and simpler integration with Eloquent models.

Railway vs AWS EC2 / VPS: Railway offers a free-tier, CI/CD automation, and database hosting in one dashboard — ideal for demo and small production use.

Service Pattern: Keeps controllers clean and focuses on SRP (Single Responsibility Principle).

Deployment Flow

Push to main triggers Railway deployment for both Laravel app and MySQL service.

.env values (DB, APP_KEY, APP_URL) are set in Railway.

Migrations auto-run on deployment using php artisan migrate --force.
