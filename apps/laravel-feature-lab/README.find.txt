
find . -name "*.yml" -o -name "*.php" -o -name "*.js" -o -name "*.sh" -o -name "*.ini" -o -name "*.conf" -o -name "Dock*" -o -path ./src/storage/framework -prune -o -path ./src/vendor -prune -o -path ./src/node_modules -prune
