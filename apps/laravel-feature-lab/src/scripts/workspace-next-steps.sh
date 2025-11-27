#!/bin/bash
# Next Steps for Laravel Feature Lab - Workspace container
# Run this inside the workspace container

set -e

echo "=== Clearing and caching config ==="
php artisan config:clear
php artisan config:cache

echo "=== Running migrations ==="
php artisan migrate

echo "=== Seeding the database (CowSeeder) ==="
php artisan db:seed --class=CowSeeder

echo "=== Register API route for CowController if not done ==="
ROUTES_FILE="routes/api.php"
if ! grep -q "Route::apiResource('cows'" "$ROUTES_FILE"; then
  echo "Route::apiResource('cows', App\Http\Controllers\CowController::class);" >> "$ROUTES_FILE"
  echo "API route added to $ROUTES_FILE"
fi

echo "=== Refreshing route cache ==="
php artisan route:cache

echo "=== Verify routes ==="
php artisan route:list | grep cows || true

echo "=== Testing API endpoints ==="
echo "Listing all cows:"
curl -s http://localhost:8080/api/cows | jq .

echo "Showing first cow (replace {id} with real ID if necessary):"
curl -s http://localhost:8080/api/cows/1 | jq .

echo "=== Optional: Clear caches if needed ==="
php artisan cache:clear
php artisan view:clear
php artisan route:clear

echo "✅ Next steps completed. Your Cow API should now be reachable."

