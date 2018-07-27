<h3>Login</h3>
<fieldset>
    <form method="post" class="jNice">
        <p>
            <label for="login">User: </label>
            <input id="login"
                    name="login" type="text"
                    value="<?php echo $this->getData("login"); ?>"
                class="text-long" />
        </p>
        <p>
            <label for="password">Password: </label>
            <input id="password"
                    name="password" type="password" value="" class="text-long" />
        </p>
        <p>
            <input type="submit" name="submit" value="Login" />
        </p>
    </form>
</fieldset>
