<?php
$new_password = '123pass'; // The user's new password
echo password_hash($new_password, PASSWORD_DEFAULT);
