#!/bin/bash
cd /warranty/html/mh/cronjobs/ && /usr/sbin/php cron.php > /warranty/html/mh/cronjobs/logs/push_mh_data_`date +\%y\%m\%d`.log 2>&1
