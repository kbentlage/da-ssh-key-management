#!/bin/bash

# Enable crontab
echo "* * * * * root /usr/local/directadmin/plugins/ssh_key_management/php/Cronjobs/cron.php" > /etc/cron.d/da-ssh-key-management_cron