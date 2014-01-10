Requirements
============

A web server with PHP support. (TODO: Check minimum PHP version.)
MySQL or MariaDB database. Any version should do. You should know:
* the location of your database (usually `localhost`)
* the name of your database
* the username and the password you use to access the database

Create a new database (optional, if you already have one)
---------------------------------------------------------

If you can and want to create user accounts and databases to your database
server, you can use e.g. phpMyAdmin to create a new database.

Or you can use the command line as follows:

    SQLUSER=usertocreate
    SQLDB=databasecreate
    read -p 'type your SQL password: ' SQLPW

The last command allows you to store the password into a variable so that it
will not be visible to other users and it will not be stored to your shell's
history.

Let's continue:

    echo "CREATE USER '$SQLUSER'@'localhost' IDENTIFIED BY '$SQLPW';
    CREATE DATABASE $SQLDB;
    GRANT USAGE ON $SQLDB . * TO '$SQLUSER'@'localhost';
    GRANT ALL PRIVILEGES ON $SQLDB . * TO '$SQLUSER'@'localhost';
    FLUSH PRIVILEGES;" | mysql -u root -p    

Your database is created.

Installation
============

Get files
---------

    git clone https://github.com/samuelmr/tracktime.git

You can put all files in the same web folder (e.g. ~/public_html/tracktime/).

Create a database config based on the sample file.

    cp dbconfig-sample.php to dbconfig.php

Edit the file.

    nano dbconfig.php

Replace the following strings
* `localhost`
* `mydatabase`
* `myusername`
* `mypassword`
with your own information. You can also modify `tracktime` if you want the
table name to be something else.

Advanced users with extra security concerns may want to move `dbconfig.php`
into a folder with no web access. The path should be included in PHP's
`include_path`.

Create database table
---------------------
You need to create a table into your database. 

    php install.php
    
Or you can look inside `install.php` and copy the SQL statement from there.
After you have created the table, you can delete (or archive) `install.php`.

That's it
---------

Try visiting index.php with your web browser. You should see a form.

Go ahead and start tracking your time!

TODO
====

The first version only allows you to store data but not much else.

Future plans (in no particular order):
* Various charts, tables and other views to the stored data
** Also comparisons to public statistics
* Offline storage and syncing with server
* Sharing your data with others
