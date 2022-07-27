# GLOBAL-MICROSERVICES-PHP APPLICATION

<!-- vscode-markdown-toc -->
* 1. [LOCAL DEVELOPMENT SETUP](#LOCALDEVELOPMENTSETUP)
	* 1.1. [(ONLY FOR LOCAL DEVELOPMENT ON WAMP) Add .htaccess file to /public](#ONLYFORLOCALDEVELOPMENTONWAMPAdd.htaccessfiletopublic)
	* 1.2. [Setup Your Local Sql Server](#SetupYourLocalSqlServer)
		* 1.2.1. [First-Time Database Setup](#First-TimeDatabaseSetup)
	* 1.3. [Connect The Laravel App To Its Databases](#ConnectTheLaravelAppToItsDatabases)
	* 1.4. [Populate Database](#PopulateDatabase)
* 2. [AZURE WEB APP INITIAL SETUP AND CONFIGURATION](#AZUREWEBAPPINITIALSETUPANDCONFIGURATION)
	* 2.1. [Intro](#Intro)
	* 2.2. [Steps to Setup Nginx on Web App](#StepstoSetupNginxonWebApp)
	* 2.3. [Modifying the default site config](#Modifyingthedefaultsiteconfig)
	* 2.4. [Creating the custom startup script](#Creatingthecustomstartupscript)
	* 2.5. [Generate the Laravel application key on the server](#GeneratetheLaravelapplicationkeyontheserver)
	* 2.6. [Updating the application settings to run our shell script](#Updatingtheapplicationsettingstorunourshellscript)
	* 2.7. [Stop, Refresh and Re-start the Web App](#StopRefreshandRe-starttheWebApp)
	* 2.8. [Open your site URL to test](#OpenyoursiteURLtotest)
* 3. [SETUP OF AZURE SQL DATABASES](#SETUPOFAZURESQLDATABASES)
	* 3.1. [Initialize A New Azure SQL Database](#InitializeANewAzureSQLDatabase)
	* 3.2. [Seeding The Default Database ('global-microservices-php')](#SeedingTheDefaultDatabaseglobal-microservices-php)
		* 3.2.1. [Migrate/Seed: 'global_microservices_php' Database](#MigrateSeed:global_microservices_phpDatabase)
		* 3.2.2. [Migrate/Seed: 'queues' Database](#MigrateSeed:queuesDatabase)
* 4. [OTHER INFORMATION](#OTHERINFORMATION)

<!-- vscode-markdown-toc-config
	numbering=true
	autoSave=true
	/vscode-markdown-toc-config -->
<!-- /vscode-markdown-toc -->

##  1. <a name='LOCALDEVELOPMENTSETUP'></a>LOCAL DEVELOPMENT SETUP

* Your local PHP version in WAMP must **exactly match** the PHP version of the Azure Web App container. As I am writing this document, the current version is v8.0.17. You will need to ensure that you are using a matching version of PHP in WAMP with the Azure Web App as you develop locally.
* add `.env` file. You can copy it from the development server, or duplicate the `.env.example` file and name it `.env`.
* in .env file, set APP_ENV=local
* php artisan key:generate
* composer install
* npm install

###  1.1. <a name='ONLYFORLOCALDEVELOPMENTONWAMPAdd.htaccessfiletopublic'></a>(ONLY FOR LOCAL DEVELOPMENT ON WAMP) Add .htaccess file to /public

```text

<IfModule mod_rewrite.c>
    <IfModule mod_negotiation.c>
        Options -MultiViews -Indexes
    </IfModule>

    RewriteEngine On

    # Handle Authorization Header
    RewriteCond %{HTTP:Authorization} .
    RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization}]

    # Redirect Trailing Slashes If Not A Folder...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_URI} (.+)/$
    RewriteRule ^ %1 [L,R=301]

    # Send Requests To Front Controller...
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteRule ^ index.php [L]

    # RedirectMatch 301 images/(.*) https://inspaffiliate.com/images/$1
</IfModule>

```

###  1.2. <a name='SetupYourLocalSqlServer'></a>Setup Your Local Sql Server

* You will need to install [SQL Server Developer Edition](https://www.microsoft.com/en-us/sql-server/sql-server-downloads) on your pc.
* You will also need the [Microsoft SQL Server Management Studio](https://docs.microsoft.com/en-us/sql/ssms/download-sql-server-management-studio-ssms). You will use this application to connect to your SQL Server database.
* Once your SQL Server is installed, you will want to create a database user named `dbadmin`. You may choose whatever password you like. To do this you will need to open the `Secuity` folder, then right-click on `Logins` folder. Choose `New Login...`.
* Finally, assign the `dbadmin` user account to all of your local databases. To do this, double-click on the `dbadmin` username to open the Login Properties window. Under 'Select A Page', choose 'User Mapping' and check the checkbox next to the database name. For the Database Role Membership, choose `db_owner` and `public`.

####  1.2.1. <a name='First-TimeDatabaseSetup'></a>First-Time Database Setup

* If it doesn't already exist, create a new/blank database called `global_microservices_php_local`.
* If it doesn't already exist, create a new/blank database called `queues_local`.

###  1.3. <a name='ConnectTheLaravelAppToItsDatabases'></a>Connect The Laravel App To Its Databases

* Duplicate the `.env.example` file and name it `.env`.
* Edit the `.env` file.
  * For each of the databases, you will need to uncomment the 'LOCAL' database variables.

###  1.4. <a name='PopulateDatabase'></a>Populate Database

If your database is brand new and never existed before, run the migrations and seed. (DO NOT DO THIS ON THE LIVE SITE!)

```php
php artisan migrate:refresh --seed
```

Otherwise, you can also use a database tool (i.e. Heidi) to copy the database from the production or development server.

##  2. <a name='AZUREWEBAPPINITIALSETUPANDCONFIGURATION'></a>AZURE WEB APP INITIAL SETUP AND CONFIGURATION

###  2.1. <a name='Intro'></a>Intro

Azure App Service on Linux images using PHP 8.x are now bundled with NGINX instead of Apache. The use of `.htaccess` files will not work for NGINX as these are used for Apache only. This will require the need to setup a custom startup script and modifying the existing NGINX site configuration.

The instructions below are taken from [this article](https://azureossd.github.io/2022/04/22/PHP-Laravel-deploy-on-App-Service-Linux-copy/index.html) and [this article](https://azureossd.github.io/2021/09/02/php-8-rewrite-rule/index.html).

###  2.2. <a name='StepstoSetupNginxonWebApp'></a>Steps to Setup Nginx on Web App

Navigate to your App Service via the Azure Portal. Under the `Development` Tools section, select `SSH` then `Go -->`.

###  2.3. <a name='Modifyingthedefaultsiteconfig'></a>Modifying the default site config

You will want to make a copy of the existing configuration and place the file inside the `/home` directory by running this command:

```bash
cp /etc/nginx/sites-available/default /home/default

```

Once copied, edit the `/home/default` file and update the section below:

```text

server {
    #proxy_cache cache;
    #proxy_cache_valid 200 1s;
    listen 8080;
    listen [::]:8080;
    root /home/site/wwwroot/public;
    index  index.php index.html index.htm;
    # server_name  example.com www.example.com;

    location / {
        index  index.php index.html index.htm hostingstart.html;
        try_files $uri $uri/ /index.php?$args;
    }

    # redirect server error pages to the static page /50x.html
    #
    error_page   500 502 503 504  /50x.html;
    location = /50x.html {
        root   /html/;
    }

    # Disable .git directory
    location ~ /\.git {
        deny all;
        access_log off;
        log_not_found off;
    }

    # Add locations of phpmyadmin here.
    location ~ [^/]\.php(/|$) {
        fastcgi_split_path_info ^(.+?\.php)(|/.*)$;
        fastcgi_pass 127.0.0.1:9000;
        include fastcgi_params;
        fastcgi_param HTTP_PROXY "";
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        fastcgi_param PATH_INFO $fastcgi_path_info;
        fastcgi_param QUERY_STRING $query_string;
        fastcgi_intercept_errors on;
        fastcgi_connect_timeout         300;
        fastcgi_send_timeout           3600;
        fastcgi_read_timeout           3600;
        fastcgi_buffer_size 128k;
        fastcgi_buffers 4 256k;
        fastcgi_busy_buffers_size 256k;
        fastcgi_temp_file_write_size 256k;
    }
}

```

###  2.4. <a name='Creatingthecustomstartupscript'></a>Creating the custom startup script

You will now need to create a custom startup script and save the file as `/home/startup.sh` with this content:

```bash

#!/bin/bash

echo "Copying custom default over to /etc/nginx/sites-available/default"

NGINX_CONF=/home/default

if [ -f "$NGINX_CONF" ]; then
    cp /home/default /etc/nginx/sites-available/default
    service nginx reload
else
    echo "File does not exist, skipping cp."
fi

# Install & Start crontab for Laravel Task Scheduler
# echo "Install & Start crontab task for Laravel Task Scheduler. Required by Laravel for its Task Scheduling to work."
# apt-get update -y
# apt-get install -y cron
# echo "* * * * * cd site/wwwroot && /usr/local/bin/php /home/site/wwwroot/artisan schedule:run >> /dev/null 2>&1" | crontab -
# service cron start

# install glances for monitoring server performance
apt-get update -y
apt-get install -y glances

```

In the above custom startup script we are doing the following:

* Overriding the existing `/etc/nginx/sites-available/default` file with the `/home/default` file.
* Reloading the NGINX service to make the updates take effect.
* Adds Task Scheduling cron job needed by the Laravel app

###  2.5. <a name='GeneratetheLaravelapplicationkeyontheserver'></a>Generate the Laravel application key on the server

You *must generate the key directly on the Azure Web App*. You will need to do this for both PRODUCTION and any DEVELOPMENT slot.

To do this, use Azure SSH and navigate to the `/home/site/wwwroot/` directory and run this command:

```bash
php artisan key:generate
```

###  2.6. <a name='Updatingtheapplicationsettingstorunourshellscript'></a>Updating the application settings to run our shell script

Navigate back to your App Service via the Azure Portal. Under the `Settings` section, select `Configuration`.

Go over to the `General Settings` section of the `Configuration` blade.

For the `Startup Command` enter the following: `/home/startup.sh`

Save these settings

###  2.7. <a name='StopRefreshandRe-starttheWebApp'></a>Stop, Refresh and Re-start the Web App

* `Stop` the Web App completely.
* `Refresh` the Web App.
* `Start` the Web App.

###  2.8. <a name='OpenyoursiteURLtotest'></a>Open your site URL to test

```text
https://{sitename}.azurewebsites.net/
```

##  3. <a name='SETUPOFAZURESQLDATABASES'></a>SETUP OF AZURE SQL DATABASES

###  3.1. <a name='InitializeANewAzureSQLDatabase'></a>Initialize A New Azure SQL Database

* In Azure Portal:
  * [ + Create A Resource ]
  * Select: Azure SQL
  * [ + Create ]

* Configure as follows:
  * Central US,
  * Version: 8.0
  * 'For small or medium size databases'
  * smallest compute size (you can grow this later)
  * storage 120 GiB, 1000 IOPS
  * storage auto-growth selected

###  3.2. <a name='SeedingTheDefaultDatabaseglobal-microservices-php'></a>Seeding The Default Database ('global-microservices-php')

* If your database is brand new and never existed before, you should run the following migrations/seeding to populate your blank database.
* WARNING: Migrations should only be run on new/empty databases. Do not run them on databases that are already in use.

####  3.2.1. <a name='MigrateSeed:global_microservices_phpDatabase'></a>Migrate/Seed: 'global_microservices_php' Database

```php
php artisan migrate:refresh --seed
```

####  3.2.2. <a name='MigrateSeed:queuesDatabase'></a>Migrate/Seed: 'queues' Database

```php
php artisan migrate --path=/database/migrations/queues_db --seed
```

##  4. <a name='OTHERINFORMATION'></a>OTHER INFORMATION

* [Guide to versioning APIs in Laravel](https://amirkamizi.com/blog/restful-api-versioning-in-laravel#When_should/shouldn%E2%80%99t_the_API_be_versioned?)
* [Migration/Seed multiple databases](https://spatie.be/docs/laravel-multitenancy/v2/installation/using-multiple-databases)
