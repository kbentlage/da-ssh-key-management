#!/bin/bash

# Create log directory
mkdir -p /usr/local/directadmin/plugins/ssh_key_management/logs

# Fix ownerships
chown -R diradmin.diradmin /usr/local/directadmin/plugins/ssh_key_management

# Fix permissions
chmod -R 0775 /usr/local/directadmin/plugins/ssh_key_management/user/*

# Run setup script
if [ $(id -u) = 0 ];
then
    ./setup/setup.sh
else
    echo "To complete the installation, run /usr/local/directadmin/plugins/ssh_key_management/setup/setup.sh as root."
fi