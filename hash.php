<?php
$newPassword = "NewAdminPassword123";
$hashedPassword = hash("sha512", $newPassword);
echo $hashedPassword;
?>