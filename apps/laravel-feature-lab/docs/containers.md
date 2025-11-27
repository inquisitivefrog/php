# Container Responsibilities

| Container        | Purpose |
|-----------------|---------|
| **app**         | PHP-FPM runtime, production-ready |
| **workspace**   | Interactive development: PHP, Composer, Node, Vite |
| **nginx**       | Reverse proxy, TLS termination |
| **postgres**    | Database storage |
| **redis**       | Caching, queues, sessions, rate limiting |
| **queue**       | Laravel queue worker (`php artisan queue:work`) |
| **scheduler**   | Cron scheduler (`php artisan schedule:run`) |
| **node**        | Vite development server |
| **mailpit**     | Local mail testing |
| **meilisearch** | Full-text search backend |

