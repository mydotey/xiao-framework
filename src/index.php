<?php
require_once "Page.php";
$appRoot = dirname(realpath(__FILE__)) . "/";
$styles = array(
    "transdmin" => "jNice/transdmin.css"
);
$scripts = array(
    "jNice" => "jNice.js",
    "base64" => "base64.min.js",
);
$page = new Page($appRoot, $styles, $scripts);
$page->init();
$page->handleBusiness();
$page->render();
