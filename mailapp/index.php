<?php
require 'config.php';
require 'includes/auth.php';
header('Location: ' . (current_user() ? 'inbox.php' : 'login.php'));
exit;