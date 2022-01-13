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

    SQLADDR=localhost
    SQLDB=mydatabase
    SQLTABLE=timetrack
    SQLUSER=myusername
    read -p 'type your SQL password: ' SQLPW

The last command allows you to store the password into a variable so that it
will not be visible to other users and it will not be stored to your shell's
history.

Let's continue:

    echo "CREATE USER '$SQLUSER'@'$SQLADDR' IDENTIFIED BY '$SQLPW';
    CREATE DATABASE $SQLDB;
    GRANT USAGE ON $SQLDB . * TO '$SQLUSER'@'$SQLADDR';
    GRANT ALL PRIVILEGES ON $SQLDB . * TO '$SQLUSER'@'$SQLADDR';
    FLUSH PRIVILEGES;" | mysql -u root -p    

You will be prompted for the database root password. Your database is created.

Installation
============

Get files
---------

    git clone https://github.com/samuelmr/tracktime.git

You can put all files in the same web folder (e.g. ~/public_html/tracktime/
or ~/Sites/tracktime).

Create database table and configuration file
--------------------------------------------

The installer script can create the database configuration file and initialize
a new table to your database.

    php install.php -l $SQLADDR -n $SQLDB -u $SQLUSER -p $SQLPW -t $SQLTABLE

Or you can manually copy `dbconfig-sample.php` to `dboconfig.php` and edit its
contents. You can also and copy the SQL statement from `install.php` and
execute them manually.

After you have created the table, you can delete (or archive) `install.php`.

Advanced users with extra security concerns may want to move `dbconfig.php`
into a folder with no web access. The path should be included in PHP's
`include_path`, though.

That's it
---------

Try visiting index.php with your web browser. You should see a form.

Go ahead and start tracking your time!

After you have inserted some values, you should see some statistics by
visiting your dashboard.html with your web browser.

TODO
====

The first version only allows you to store data but not much else.

Future plans (in no particular order):
* Comparisons to public statistics
* Mobile client with calendar integration and location tracking 
* Offline storage and syncing with server
* Sharing your data with others
