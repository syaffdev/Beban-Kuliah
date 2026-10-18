# BebanKuliah

**REST API for Academic Workload Management**

BebanKuliah is a backend application designed to help students manage academic tasks, monitor workload, assess deadlines, and organize their available study time.

The project provides a RESTful API built with Laravel, with authentication and user-scoped data access. It is designed to support a separate frontend application and future academic scheduling features.

## Table of Contents

- [Overview](#overview)
- [Features](#features)
- [Technology Stack](#technology-stack)
- [Requirements](#requirements)
- [Installation](#installation)
- [Environment Configuration](#environment-configuration)
- [Database Setup](#database-setup)
- [Running the Application](#running-the-application)
- [API Documentation](#api-documentation)
- [Authentication](#authentication)
- [Testing](#testing)
- [Security](#security)
- [Project Structure](#project-structure)
- [Development Guidelines](#development-guidelines)
- [Production Deployment Checklist](#production-deployment-checklist)
- [Project Status](#project-status)
- [License](#license)

## Overview

Managing academic responsibilities can become difficult when students have multiple courses, assignments, deadlines, and competing priorities.

BebanKuliah aims to provide a centralized backend for academic workload management. Its API supports course and task management, workload analysis, and the management of weekly availability and schedule exceptions.

The backend is developed independently from the frontend to improve separation of concerns, maintainability, and collaboration.

## Features

### Authentication and Access Control

- User registration and login.
- Token-based authentication using Laravel Sanctum.
- Authenticated logout and token revocation.
- Request rate limiting for authentication endpoints.
- User-scoped access to academic data.

### Course Management

- Create, retrieve, update, and delete courses.
- Validate course data, including credit values.
- Enforce ownership so users can manage only their own courses.

### Task Management

- Create, retrieve, update, and delete academic tasks.
- Associate tasks with courses.
- Store task titles, descriptions, deadlines, priorities, and statuses.
- Validate task data and course ownership.
- Prevent unauthorized access to another user's tasks.

### Workload Analysis

- API endpoint for analyzing academic workload.
- Task analysis to support future workload and deadline-risk assessment features.

### Availability Management

- Manage recurring weekly availability.
- Create and manage schedule exceptions.
- Support unavailable periods and additional availability.
- Validate availability data according to the exception type.

### Automated Testing

- Feature tests for authentication flows.
- Security tests for ownership and access control.
- Validation tests for course and task operations.
- Tests for weekly availability and schedule exceptions.

## Technology Stack

| Component | Technology |
|---|---|
| Backend framework | Laravel 11 |
| Programming language | PHP 8.3 |
| API authentication | Laravel Sanctum |
| Database | MySQL |
| Dependency management | Composer |
| Testing | PHPUnit / Laravel testing tools |
| Version control | Git |

The frontend is planned as a separate React, TypeScript, and Vite application. Frontend integration is not part of this backend setup guide.

## Requirements

Ensure the following tools are installed:

- PHP 8.3 or a compatible version supported by the project.
- Composer.
- MySQL.
- Git.
- A web server or Laravel development server.

Check your installed versions:

```bash
php -v
composer --version
git --version
```

Check available PHP extensions against Laravel and the installed dependencies if installation fails.

## Installation

### 1. Clone the repository

```bash
git clone <REPOSITORY_URL>
cd BebanKuliah
```

Replace `<REPOSITORY_URL>` with the actual repository URL.

### 2. Install dependencies

```bash
composer install
```

For local development, install the dependencies specified in the project's Composer configuration. Do not use development dependency flags that remove required packages.

### 3. Configure the environment

Create a local environment file:

```bash
cp .env.example .env
```

On Windows PowerShell, use:

```powershell
Copy-Item .env.example .env
```

Generate the application key:

```bash
php artisan key:generate
```

Do not commit `.env` or any file containing real credentials, application secrets, or access tokens.

## Environment Configuration

Configure the database connection and application settings in `.env`.

Example local configuration:

```dotenv
APP_NAME=BebanKuliah
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_BebanKuliah
DB_USERNAME=root
DB_PASSWORD=
```

The database name, username, and password must match the local MySQL configuration.

**Important:** The example above is intended for local development. Production environments require appropriate credentials, `APP_DEBUG=false`, and a valid HTTPS application URL. Never deploy a production environment using development secrets or an exposed debug page.

## Database Setup

Create the database using MySQL or your preferred database management tool.

Run migrations:

```bash
php artisan migrate
```

Inspect migration status:

```bash
php artisan migrate:status
```

The project includes migrations for:

- Users and authentication tokens.
- Courses.
- Tasks.
- Weekly availability.
- Availability exceptions.

For a local development environment, seeders can be used when appropriate:

```bash
php artisan db:seed
```

Review the available seeders before running them. Do not run development seeders against a production database without explicitly verifying their behavior.

**Warning:** `php artisan migrate:fresh` drops existing tables before recreating them. Never use it on a database containing data you need to preserve.

## Running the Application

Start the Laravel development server:

```bash
php artisan serve
```

By default, the application is available at:

```text
http://127.0.0.1:8000
```

Verify registered API routes:

```bash
php artisan route:list --path=api
```

For availability endpoints:

```bash
php artisan route:list --path=api/availability
```

The root URL is not necessarily an API health endpoint. Use the registered API routes to verify available functionality.

## API Documentation

The API uses the `/api` prefix.

### Authentication

| Method | Endpoint | Purpose |
|---|---|---|
| POST | `/api/auth/register` | Register a user |
| POST | `/api/auth/login` | Authenticate a user |
| POST | `/api/auth/logout` | Revoke the authenticated token |

### Courses

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/courses` | Retrieve courses |
| POST | `/api/courses` | Create a course |
| GET | `/api/courses/{id}` | Retrieve a specific course |
| PATCH | `/api/courses/{id}` | Update a course |
| DELETE | `/api/courses/{id}` | Delete a course |

### Tasks

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/tasks` | Retrieve tasks |
| POST | `/api/tasks` | Create a task |
| GET | `/api/tasks/{id}` | Retrieve a specific task |
| PATCH | `/api/tasks/{id}` | Update a task |
| DELETE | `/api/tasks/{id}` | Delete a task |

### Workload Analysis

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/analysis/workload` | Retrieve workload analysis |

### Weekly Availability

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/availability/weekly` | Retrieve weekly availability |
| POST | `/api/availability/weekly` | Create a weekly availability entry |
| PATCH | `/api/availability/weekly/{id}` | Update a weekly availability entry |
| DELETE | `/api/availability/weekly/{id}` | Delete a weekly availability entry |

### Availability Exceptions

| Method | Endpoint | Purpose |
|---|---|---|
| GET | `/api/availability/exceptions` | Retrieve availability exceptions |
| POST | `/api/availability/exceptions` | Create an availability exception |
| PATCH | `/api/availability/exceptions/{id}` | Update an availability exception |
| DELETE | `/api/availability/exceptions/{id}` | Delete an availability exception |

Confirm the current route definitions with `php artisan route:list --path=api` before relying on this table as an exhaustive API reference.

### Request and Response Conventions

- Use JSON request bodies for JSON-based endpoints.
- Send `Accept: application/json` when an API JSON response is expected.
- Use the HTTP method appropriate to the requested operation.
- Expect validation errors to be returned as structured JSON responses.
- Authenticated endpoints require a valid access token.

Example request headers:

```http
Accept: application/json
Content-Type: application/json
Authorization: Bearer <ACCESS_TOKEN>
```

The authorization header is required only for protected endpoints.

## Authentication

The API uses Laravel Sanctum personal access tokens for API authentication.

After a successful login, use the returned token to access protected endpoints:

```http
Authorization: Bearer <ACCESS_TOKEN>
```

When logging out, the authenticated token should be revoked according to the application's logout implementation.

Never publish a real access token in documentation, screenshots, test fixtures, issue reports, or version control.

## Testing

Run the complete test suite:

```bash
php artisan test
```

Run a specific feature test:

```bash
php artisan test --filter=AvailabilityTest
```

Run availability exception tests:

```bash
php artisan test --filter=AvailabilityExceptionTest
```

Before merging or deploying changes, ensure the relevant tests pass and investigate failures rather than bypassing them.

Tests should cover:

- Authentication success and failure cases.
- Input validation.
- Resource ownership and authorization.
- Course and task relationships.
- Availability rules and exception validation.
- Regression risks introduced by changes.

## Security

Security is a core development requirement for BebanKuliah.

Current security-oriented implementation and testing include:

- Authentication for protected API routes.
- Rate limiting on authentication endpoints.
- Validation of incoming data.
- Ownership checks for user-scoped resources.
- Restrictions on mass assignment of sensitive ownership fields.
- Automated tests for unauthorized resource access.

These controls do not replace a complete security assessment.

Before public deployment, review:

- Authorization and object-level access controls across all endpoints.
- SQL injection and other injection risks.
- Authentication, brute-force resistance, and token lifecycle.
- Sensitive information in logs, responses, and repository history.
- Dependency vulnerabilities and patch status.
- HTTPS, CORS, database permissions, backups, and monitoring.

Do not treat passing automated tests as proof that the application is free from security vulnerabilities.

## Project Structure

The backend follows Laravel's application structure.

```text
BebanKuliah/
├── app/
│   ├── Http/
│   │   └── Controllers/
│   │       └── Api/
│   └── Models/
├── database/
│   ├── migrations/
│   ├── factories/
│   └── seeders/
├── routes/
│   └── api.php
├── tests/
│   └── Feature/
├── .env.example
├── artisan
├── composer.json
└── README.md
```

Key directories:

- `app/Http/Controllers/Api/`: API request handling and application workflows.
- `app/Models/`: Eloquent models and relationships.
- `database/migrations/`: database schema definitions.
- `database/seeders/`: optional development and test data setup.
- `routes/api.php`: API route registration.
- `tests/Feature/`: feature and API behavior tests.

## Development Guidelines

To maintain code quality and project consistency:

1. Keep controllers focused on request handling and application workflows.
2. Validate user input on the server.
3. Enforce resource ownership on every relevant operation.
4. Avoid trusting client-supplied ownership identifiers.
5. Add tests for new behavior and security-sensitive changes.
6. Keep secrets and local configuration out of version control.
7. Review database migrations before applying them to shared or production environments.
8. Document API behavior when adding or changing endpoints.

## Production Deployment Checklist

Before deploying to a public environment, verify the following.

### Application configuration

- [ ] Set `APP_ENV=production`.
- [ ] Set `APP_DEBUG=false`.
- [ ] Configure a valid HTTPS `APP_URL`.
- [ ] Generate and securely manage the application key.
- [ ] Configure production database credentials using a secure secrets mechanism.
- [ ] Confirm `.env` and secrets are not publicly accessible.

### Database and infrastructure

- [ ] Configure least-privilege database access.
- [ ] Apply reviewed migrations through the deployment process.
- [ ] Establish and test database backups and restoration procedures.
- [ ] Configure the production web server and PHP runtime.
- [ ] Ensure writable directories have appropriate permissions.
- [ ] Configure logs, monitoring, and error reporting.

### Application security and quality

- [ ] Run the complete automated test suite.
- [ ] Review authentication and authorization behavior.
- [ ] Verify CORS and allowed origins for the deployed frontend.
- [ ] Review dependencies for known vulnerabilities.
- [ ] Conduct a security assessment before public release.
- [ ] Confirm no development credentials or test tokens are exposed.

### Deployment verification

- [ ] Verify API routes and authentication in the deployed environment.
- [ ] Test critical workflows after deployment.
- [ ] Confirm error responses do not expose stack traces or secrets.
- [ ] Verify rollback and recovery procedures.

A completed checklist should be supported by actual verification. The presence of this checklist does not itself certify production readiness.

## Project Status

BebanKuliah is under active development.

Implemented backend areas include authentication, course management, task management, workload analysis endpoints, weekly availability, and availability exceptions.

Future development may include a rule-based study schedule recommendation engine, schedule simulation, frontend integration, and additional security testing.

Features should be considered complete only when their implementation, validation rules, tests, and API behavior have been verified.

## License

No license has been specified yet.

Before distributing or accepting external contributions, add an appropriate `LICENSE` file and update this section to reflect the project's actual licensing terms.
