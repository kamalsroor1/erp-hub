#!/bin/bash
cd /home/u910151740/domains/sroor.baraa-solutions.com/public_html || exit 1
/usr/bin/php artisan schedule:run >> storage/logs/cron.log 2>&1
