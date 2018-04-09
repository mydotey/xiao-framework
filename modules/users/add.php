<h3>Add New User</h3>
<fieldset>
    <form method="post" class="jNice">
        <p>
            <label for="login">Login: </label>
            <input name="login" id="login" type="text" value="<?php echo $this->getData("login"); ?>" class="text-long" />
        </p>
        <p>
            <label for="password">Password: </label>
            <input name="password" id="password" type="text" value="<?php echo $this->getData("password"); ?>" class="text-long" />
        </p>
        <p>
            <label for="email">Email: </label>
            <input name="email" id="email" type="text" value="<?php echo $this->getData("email"); ?>" class="text-long" /></p>
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
        <p class="submit"><input type="submit" value="Add" /></p>
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
