<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8;" />
        <title><?php echo $this->getTitle(); ?></title>
        <?php
        $this->applyStyles();
        $this->applyScripts();
        ?>
        <!--[if IE 6]><link rel="stylesheet" type="text/css" media="screen" href="scripts/jNice/ie6.css" /><![endif]-->
        <!--[if IE 7]><link rel="stylesheet" type="text/css" media="screen" href="scripts/jNice/ie7.css" /><![endif]-->
    </head>
<body>
    <div id="wrapper">
        <h1>
            <a href="<?php echo $this->webRoot . "index.php"; ?>">
                <span>Pop This</span>
            </a>
        </h1>
        <!-- You can name the links with lowercase, they will be transformed to uppercase by CSS, we prefered to name them with uppercase to have the same effect with disabled stylesheet -->
        <?php
        $mainNavContent = "";
        foreach($menu as $item)
        {
            $mainNavContent .= sprintf('<li><a href="%s"%s>%s</a></li>',
                $item["url"], $item["isActive"] ? ' class="active"' : "", $item["title"]);
        }
        if($this->isLoggedIn())
        {
            $mainNavContent .= <<<html
                <li class="logout"><a href="{$this->webRoot}index.php?module=users&action=logout">LOGOUT</a></li>
html;
        }
        if($mainNavContent !== "")
        {
            echo '<ul id="mainNav">' . $mainNavContent . '</ul>';
        }
        ?> <!-- // #end mainNav -->

        <div id="containerHolder">
            <div id="container" <?php if($this->getData("noSidebar")) echo ' class="no-sidebar"'; ?>>
                <?php
                if(!$this->getData("noSidebar"))
                {
                ?>
                <div id="sidebar">
                    <?php 
                    if(!empty($subMenu))
                    {
                        ?>
                    <ul class="sideNav">
                    <?php
                    foreach($subMenu as $item)
                    {

                        printf('<li><a href="%s"%s>%s</a></li>',
                             $item["url"], $item["isActive"] ? ' class="active"' : '', $item["title"]
                        );
                    }
                    ?>
                    </ul>
                    <?php
                    }
                    ?> <!-- // .sideNav -->
                </div>
                <!-- // #sidebar -->
                <?php
                }
                ?>

                <div id="main">
