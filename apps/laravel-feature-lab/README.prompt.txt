
I am working as a Software Engineer. I need to use PHP on an upcoming project. I haven’t used it since 2013 so quite likely many things have changed. You suggested developing an application that demonstrates use of PHP and Laravel with PostGreSQL so that I can become familiar with the many Lavarel features such as Pennant, Cashier, Breeze and others. I have a Ubuntu 24.04.3 VM with 12GB RAM, 15GB remaining disk and 8 CPU Cores on a MacBook Air with 20 FPUs and 24GB RAM. We are attempting to follow best practices and take appropriate security measures. We have the following layout so far. You have been providing file content as if this is old hat so I am dependent on your expertise. We have worked together for several days. This is what we have completed so far. If you need to see file content for clarity, just ask. We are trying to be both secure and state-of-the-art regarding Best Practices while showing off the Laravel features so feel free to offer suggestions. There is some history regarding decisions as well so please ask before speculating as it will save time. We appear to have a Laravel basic environment configured and deployed in Docker so php extensions and npm packages have been installed during build to ensure rapid deployment of new instances when auto scaling or replacing failed instances. We are now building an application that demonstrates the various features of the framework so I can gain familiarity? I would like feature demonstrated by app and validated by tests. You suggested these Laravel options in this image. I would also like to use current CI Pipeline tools for automated testing, syntax checking, static analysis, memory leaks, load testing, etc. I will rely on you to introduce me to them. 

sre@cpp:~/laravel-feature-lab$ pwd /home/sre/laravel-feature-lab sre@cpp:~/laravel-feature-lab$ 

find . -name "*.yml" -o -name "*.php" -o -name "*.js" -o -name "*.sh" -o -name "*.ini" -o -name "*.conf" -o -name "Dock*" -o -name "php*" -o -path ./src/storage/framework -prune -o -path ./src/vendor -prune -o -path ./src/node_modules -prune 

docker compose exec workspace bash 
composer show --direct | grep -v "laravel/framework" | grep -v "php " 

php vendor/bin/phpstan analyse --memory-limit=1G
./vendor/bin/pint 
php vendor/bin/php-cs-fixer fix --cache-file=/tmp/.php_cs.cache --dry-run --diff --verbose

