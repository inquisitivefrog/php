
USE THESE COMMANDS
------------------
01. docker compose run --rm workspace php artisan package:discover
02. docker compose run --rm workspace php artisan migrate:status
03. docker compose run --rm workspace php artisan schema:dump    
04. docker compose run --rm workspace php artisan route:list 
05. docker compose run --rm workspace php artisan route:list --except-vendor --path=api --method=GET
06. docker compose run --rm workspace php artisan schedule:list
07. docker compose run --rm workspace php artisan schedule:work
08. docker compose run --rm workspace php artisan vendor:publish
09. docker compose run --rm workspace php artisan view:cache 
10. docker compose run --rm workspace php artisan model:show user
11. docker compose run --rm workspace php artisan db:show
12. docker compose run --rm workspace php artisan db:monitor
13. docker compose run --rm workspace php artisan config:publish
14. docker compose run --rm workspace php artisan scout:sync-index-settings

