#### testing php74

    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/captcha && exec bash"

    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/captcha && composer update"
    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/captcha && composer install"
    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/captcha && sh phpunit"
    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/captcha && git status"
    docker exec -it dev-php74 sh -c "cd /var/www/php74/yusam-hub/captcha && git pull"

å