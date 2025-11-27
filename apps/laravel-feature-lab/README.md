# Laravel Feature Lab

**A working Laravel 11 application demonstrating modern PHP development, containerized with Docker.**

---

## Core Setup

- **PHP:** 8.3  
- **Composer:** 2.x  
- **Laravel:** 11  
- **PostgreSQL:** 16  
- **Redis:** Queues, caching, sessions, rate limiting  
- **Docker + Docker Compose:** Containerized development environment

---

## Purpose

This project demonstrates various Laravel features, including:

- Authentication scaffolding (Breeze)  
- Feature flagging (Pennant)  
- Subscription billing (Cashier/Stripe)  
- Queue management and dashboards (Horizon)  
- Real-time debugging (Telescope)  
- Search abstraction (Scout)  
- Notifications (Email/Slack/SMS)  

It also includes a SCRUD demo module for practicing model, controller, request, resource, and seeder creation.

---

## Dockerized Components

| Container        | Purpose |
|-----------------|---------|
| **app**         | PHP-FPM runtime for Laravel |
| **workspace**   | Development container for running Artisan, Composer, Node, Vite, jq, yq interactively |
| **nginx**       | Reverse proxy & TLS termination |
| **postgres**    | Database |
| **redis**       | Cache, queues, sessions, rate limiting |
| **queue**       | Dedicated Laravel queue worker |
| **scheduler**   | Cron scheduler for `artisan schedule:run` |
| **node**        | Vite development server |
| **mailpit**     | Local email testing |
| **meilisearch** | Search backend for Scout |

> **Note on `workspace` container:**  
> For development and interactive commands, **always use the workspace container**. It includes Node, npm, Vite, PHP, Composer, and useful debugging tools (`jq` and `yq`).  
> Files created in the workspace are automatically synced to the host via the bind mount (`./src:/var/www/html`).

Example usage:

```bash
docker compose exec workspace bash
php artisan make:model Cow -m
npm run dev
vite

