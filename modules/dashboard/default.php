<h3>Account Infomation</h3>
<fieldset>
    <form class="jNice" method="post">
        <p>
            <label for="email">Email: </label>
            <input id="email" name="email" value="<?php echo $me["email"]; ?>" class="text-long" />
        </p>
        <p class="submit"><input name="save" type="submit" value="Update" /></p>
    </form>
    <script type="text/javascript">
        jQuery("form").submit(function(){
            var email = jQuery("#email").val();
            if(isEmpty(email))
            {
                alert("Email could not be empty.");
                return false;
            }
            if(!isEmail(email))
            {
                alert("Email format is not correct.");
                return false;
            }
        });
    </script>
</fieldset>
