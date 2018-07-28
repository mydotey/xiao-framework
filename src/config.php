<?php
$config = array(
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

    // Modules
    // menuOrder is required, and unique. an action also may be a main menu item if has a attribute "asMainMenu" with value true.
    "modules" => array(
        "users" => array(
            "title" => "Users",
            "capability" => "users",
            "menuOrder" => 0,
            "actions" => array(
                "default" => array(
                    "title" => "User List",
                    "asSubMenu" => true,
                    "menuOrder" => 0
                ),
                "add" => array(
                    "title" => "Add User",
                    "asSubMenu" => true,
                    "menuOrder" => 1
                ),
                "edit" => array(
                    "title" => "Edit User"
                ),
                "login" => array(
                    "title" => "Log In",
                    "capability" => "anonymous"
                ),
                "logout" => array(
                    "title" => "Log Out",
                    "capability" => "anonymous"
                )
            )
        ),
        "settings" => array(
            "title" => "Settings",
            "capability" => "settings",
            "menuOrder" => 90,
            "actions" => array(
                "default" => array(
                    "title" => "Setting",
                    "asSubMenu" => true,
                    "menuOrder" => 0
                )
            )
        ),
        "dashboard" => array(
            "title" => "Dashboard",
            "capability" => "dashboard",
            "menuOrder" => 100,
            "actions" => array(
                "default" => array(
                    "title" => "Account Info",
                    "asSubMenu" => true,
                    "menuOrder" => 0
                ),
                "change-password" => array(
                    "title" => "Change Password",
                    "asSubMenu" => true,
                    "menuOrder" => 1
                ),
            )
        ),
    ),

    // Default Page
    "defaultPage" => array(
        "users" => array("module" => "users"),
        "settings" => array("module" => "settings"),
        "dashboard" => array("module" => "dashboard"),
        "anonymous" => array("module" => "users", "action" => "login")
    ),

    // Paging
    "pageSize" => 30,
);
