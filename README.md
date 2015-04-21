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