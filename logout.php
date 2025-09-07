<?php
require __DIR__ . '/auth.php';

do_logout();

header('Location: login.php');
exit;
