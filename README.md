# DirectAdmin SSH Key Management Plugin
Welcome to this repository of an unofficial DirectAdmin plugin for managing SSH keys. 

With this plugin end-users on an DirectAdmin server can easliy add and remove their public SSH key(s) to their user. 

The problem with DirectAdmin is that users can only logon to SSH with a plain-text password, something we don't want at all. Previously we managed the SSH-keys for customers manually which was a time-consuming job. So I decided to create this plugin for it, so users can do this by themselves.

I developed and used this plugin for over a year now on our own servers, but I decided to release it to the public! So everyone can use this.

# Installation
```
cd /usr/local/directadmin/plugins
git clone https://github.com/kbentlage/da-ssh-key-management.git ssh_key_management
sh ssh_key_management/scripts/install.sh
```

# Update
```
cd /usr/local/directadmin/plugins/ssh_key_management
git pull
```

# CSF firewall integration (14-03-2018)
By default we have blocked public SSH access and we're using CSF as default firewall. I've integrated CSF so that end-users now can "whitelist" their IP-adresses for SSH access.

# Screenshots
Add SSH key

![Add SSH key](https://raw.githubusercontent.com/kbentlage/da-ssh-key-management/master/screenshots/add.png)

List SSH keys

![List SSH keys](https://raw.githubusercontent.com/kbentlage/da-ssh-key-management/master/screenshots/list.png)
