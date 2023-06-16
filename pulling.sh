#!/bin/bash
# Pulling the latest version of the code from the repository
touch pulling.log

echo 'starting pulling.sh' &> pulling.log

echo "User: $(whoami)" &>> pulling.log

echo 'below should print something like usr/bin:usr/local/bin...' &>> pulling.log

echo $PATH &>> pulling.log

echo 'below should print the path of this file' &>> pulling.log

export APP_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

echo $APP_DIR &>> pulling.log

echo 'below should print the result of git pull' &>> pulling.log

#adding the app dir to git safe directories
git config --global --add safe.directory $APP_DIR

git pull &>> pulling.log

echo 'below should print composer path' &>> pulling.log

# set COMPOSER_HOME to the root dir of this file
export COMPOSER_HOME=$APP_DIR

which composer &>> pulling.log

echo 'below should print composer results' &>> pulling.log

composer install &>> pulling.log

echo 'below should print php artisan migrate results' &>> pulling.log

php artisan migrate --force &>> pulling.log

echo 'below should print npm path' &>> pulling.log

which npm &>> pulling.log

echo 'below should print npm install results' &>> pulling.log

npm install &>> pulling.log

echo 'below should print npm run prod results' &>> pulling.log

npm run prod &>> pulling.log

echo 'below should print npm run build results' &>> pulling.log

npm run build &>> pulling.log
#return success
exit 0

