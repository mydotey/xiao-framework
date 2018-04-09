<?php
self::deleteSession("currentUser");
$this->setMessage("You have logged out.");
header("Location: " . $this->webRoot . "index.php?module=users&action=login");
exit;
