#!/usr/bin/env bash
/lib/systemd/systemd
# RELOAD DAEMON
systemctl daemon-reload
# STARTING PHP FPM
service php7.4-fpm restart
# STARTING NGINX
service nginx restart
# STARTING MYSQL
service mysql restart
# STARTING REDIS
service redis-server restart
# MIGRATE DB
/usr/bin/php /var/www/app/artisan migrate
