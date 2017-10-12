#!/bin/bash

# Create log directory
mkdir /usr/local/directadmin/plugins/ssh_key_management/logs

# Fix ownerships
chown -R diradmin.diradmin /usr/local/directadmin/plugins/ssh_key_management

# Fix permissions
chmod -R 0775 /usr/local/directadmin/plugins/ssh_key_management/user/*
