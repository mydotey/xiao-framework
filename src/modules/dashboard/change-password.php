<h3>Change Password</h3>
<fieldset>
    <form method="post" class="jNice">
        <p>
            <label for="oldPassword">Old Password: </label>
            <input type="password" name="oldPassword" value="" class="text-long" />
        </p>
        <p>
            <label for="newPassword">New Password: </label>
            <input type="password" name="newPassword" value="" class="text-long" />
        </p>
        <p>
            <label for="confirmPassword">Confirm Password: </label>
            <input type="password" name="confirmPassword" value="" class="text-long" />
        </p>
        <p class="submit">
            <input type="submit" name="save" value="Change Password" />
        </p>
    </form>
    <script type="text/javascript">
        jQuery("form").submit(function(){
            var newPassword = jQuery(":password[name=newPassword]").val();
            var confirmPassword = jQuery(":password[name=confirmPassword]").val();
            if(newPassword != confirmPassword)
            {
                alert("The new password and confirm password are not the same.");
                return false;
            }
        });
    </script>
</fieldset>
