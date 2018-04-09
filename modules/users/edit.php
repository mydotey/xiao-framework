<h3>Edit User</h3>
<fieldset>
    <form method="post" class="jNice">
        <p>
            <label for="login">Login: </label>
            <input id="login" name="login" type="text" value="<?php echo $login; ?>" disabled="disabled" class="text-long" />
        </p>
        <p>
            <label for="password">Password: </label>
            <input id="password" name="password" type="text" value="<?php echo $password; ?>" class="text-long" />
            <span class="field-desc">If don't want to change, leave it emtpy.</span>
        </p>
        <p>
            <label for="email">Email: </label>
            <input id="email" name="email" type="text" value="<?php echo $email; ?>" class="text-long" />
        </p>
        <p class="checkbox-list">
            <label>Capabilities</label>
            <span class="list">
                <?php
                $capabilities = $this->getData("capabilities");
                if(empty($capabilities))
                    $capabilities = array();
                $i = 0;
                foreach($completeCapabilities as $item)
                {
                    printf('<input type="checkbox" value="%s" name="capabilities[]" id="%s" %s /><label for="%s">%s</label>',
                        $item, $item, in_array($item, $capabilities) ? ' checked="checked"' : '', $item, $item);
                    if(++$i % 5 == 0)
                        echo '<br />';
                }
                ?>
            </span>
        </p>
        <p class="submit"><input type="submit" value="Save" /></p>
    </form>
    <script type="text/javascript">
        jQuery("form").bind("submit", function(){
            if(isEmpty(jQuery("#login").val()))
            {
                alert("User Login could not be empty.");
                return false;
            }
            if(!isEmpty(jQuery("#email").val()) && !isEmail(jQuery("#email").val()))
            {
                alert("Email format is not correct.");
                return false;
            }
            if(jQuery(":checkbox[name=capabilities[]]:checked").length == 0)
            {
                alert("Please designate the user a capability.");
                return false;
            }
        });
    </script>
</fieldset>
