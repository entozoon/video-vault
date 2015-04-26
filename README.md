# video-vault
Intelligently lists your video files and helps you to know which ones you've already watched

# Before you start
Have a quick glance through this readme, and if it looks too complicated.. then this project probably isn't for you!

# Requirements
Must be run on an apache server on the computer that contains the video files

# Installation
- Apache and MySQL MUST be running from commandline not wamp or whatever.
  You might wish to look at the file _Start_Apache_and_Mysql_Example.bat which is an example batch script to start Apache and MySQL for someone who has them installed via Wamp.

- Make sure your apache is accessible from another computer/phone on the network, if not.. you may need to open up httpd.conf and check it has
```
Listen *:80
```
And then also, further down (search for onlineoffline)
```
#   onlineoffline tag - don't remove
	 Require local
	 Require ip 192.168
```

- Install the database by loading up phpmyadmin and importing \assets\db\video-helper.sql

- Have a look through config.php and set it up as you require, i.e. with your local database login

# Usage
- Run _Start_Apache_and_Mysql_Example.bat or however you've decided to start Apache and MySQL on commandline

This is a local running site, not something to be uploaded to a server.
My advice is to use the fantastic xip.io service to allow easy access from any networked computer/phone/device:
In your extra/httpd-vhosts.conf file (enabled in httpd.conf), create the vhost such that it looks like..

```
#### VideoVault VirtualHost ####
<VirtualHost *:80>
    DocumentRoot "E:/www/m.ichael/video-vault"
    ServerAlias vv.*.xip.io
    ServerName vv.dev
</VirtualHost>
```

http://vv.192.168.1.65.xip.io/


# Plusnet/BT #
Plusnet is a fucker, and so is BT. Such that their DNS blocks xip from working properly.
If you have those ISPs and are on a thomson router, you have to use telnet (seriously) to add Google's DNS servers.
Don't worry though, we can just use putty if telnet isn't installed.
In my case, open up putty, tick the telnet box and stick in 192.168.1.254 - port 22
Login, then run these commands
```
dns server route flush
dns server route add dns=8.8.8.8 metric=0 intf=Internet
dns server route add dns=8.8.4.4 metric=0 intf=Internet
dns server route list
saveall
```
(ignore any unknown command things, should #justwork)


# Automatic Updating #
Being a local site, we can't just set up a cron job, but on Windows a Task Scheduler event will do just fine.
Open up Task Scheduler and Create Task.
General - Run when logged on, click Change User or Group and type SYSTEM into the textarea (makes it run in background)
Triggers - New - Begin the task: At log on, Repeat task every: 1 hour, for a duration of: Indefinitely
Actions - Start a program: php - with the argument being the location of the regenerateVideos.php file, Start in: the root directory of video-vault