# Budget Manager - Project Overview

## Purpose
Budget Manager is an MVP (Minimum Viable Product) personal budget management application built with PHP and Symfony 6.4 LTS. It allows users to manage personal budgets and track income/expense transactions through both web interface and REST API.

## Core Features
- **User Management**: Registration, login, JWT authentication
- **Budget Management**: Create and manage personal budgets
- **Transaction Tracking**: Track income and expenses for each budget
- **Web Interface**: Responsive Bootstrap-based frontend
- **REST API**: Complete API with OpenAPI documentation
- **Real-time Balance**: Automatic balance calculations

## Architecture Overview
The application follows Symfony best practices with:
- **MVC Architecture**: Controllers, entities, and Twig templates
- **RESTful API**: JSON responses with proper HTTP status codes
- **JWT Authentication**: Separate authentication for API and web
- **Database First**: Doctrine ORM with migrations and fixtures
- **Asset Pipeline**: Webpack Encore with Bootstrap and Stimulus
- **Documentation**: Automatic OpenAPI/Swagger generation

## Key Entities
1. **User**: Authentication and user management with `isActive` flag
2. **Budget**: User-owned budget containers with balance calculation
3. **Transaction**: Income/expense records linked to budgets

## Tech Stack
- **Backend**: PHP 8.1+, Symfony 6.4 LTS
- **Database**: SQLite (development), supports PostgreSQL/MySQL
- **Frontend**: Bootstrap 5, Stimulus JavaScript framework, SCSS
- **Authentication**: JWT tokens with RSA encryption
- **Documentation**: OpenAPI 3.0 with automatic generation
- **Build Tools**: Composer, Webpack Encore, npm

## Development Status
✅ Complete MVP with all core functionality
✅ Database migrations and fixtures
✅ Custom database reset command
✅ Comprehensive API documentation
✅ Responsive web interface
✅ Security best practices implemented