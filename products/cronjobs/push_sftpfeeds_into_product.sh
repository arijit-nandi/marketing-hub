#!/bin/bash
cd /warranty/html/products/cronjobs/ && /usr/bin/php cron.php > /warranty/html/products/cronjobs/logs/process_sftp_feeds_`date +\%y\%m\%d`.log 2>&1