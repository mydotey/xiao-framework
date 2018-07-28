# Xiao Framework

a light-weight PHP web application development framework

## requirement

PHP 5, MySQL 5

Cannot be used in PHP 7 (mysql_connect is removed)

## usage

1. create a database <db_name>, init its structrue by xiao-init.sql
2. edit config.php, change dbConfig/install/webRoot 
```php
    // Database
    "dbConfig" => array(
        "server" => "localhost",
        "user" => "root",
        "password" => "<db_password>",
        "name" => "<db_name>"
    ),
    // Install Settings - Available at Installation Only
    "install" => array(
        "admin-login" => "admin",
        "admin-password" => "123456",
        "admin-email" => "master@site.com",
        "admin-capabilities" => array(
            "dashboard", "settings", "users"
        )
    ),

    // Debug
    "debug" => true,
    "displayErrors" => true,
    "errorReporting" => E_ERROR | E_WARNING | E_PARSE | E_NOTICE, // or E_ALL

    // Site
    "webRoot" => "http://<host>/<web_root>",
    "title" => "<site_title>",
```
3. run first install: http://<host>/<web_root>?module=install, the configured install admin user will be inited in the db_name

4. after install, remove the modules/install folder 

5. visit http://<host>/<web_root>, log in with the admin user

## development

read xiao-documentation.doc

### sample

see xiao-sample.zip

