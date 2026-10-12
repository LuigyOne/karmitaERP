<?php
$options = [
    // Increase the bcrypt cost from 12 to 13.
    'cost' => 13,
];
echo password_hash("Admin", PASSWORD_BCRYPT, $options);
?>