<h3>Application Installation.</h3>
<fieldset>
    <?php
if($this->getData("dbConnectionInfo"))
{
    echo '<p>' . $dbConnectionInfo["info"] . '</p>';
    if($dbConnectionInfo["status"])
    {
        echo <<<html
    <p>Are you sure you want to install the application? If the database has data, they will be dropped.</p>
    <form class="jNice" method="post">
        <p><input type="submit" name="install-submit" value="install" /></p>
    </form>
html;
    }
    else
    {
        echo <<<html
    <p>Please check your configuration.</p>
html;
    }
}
else
{
    $siteUrl = $this->getPage(array(), array("module", "action"));
    echo <<<html
    <p>Congratulatons. Your application has been installed successfully.</p>
    <p>Administrator: $login</p>
    <p>Password: $password</p>
    <p><a href="$siteUrl">Click Here to Use.</a></p>
html;
}
?></fieldset>
