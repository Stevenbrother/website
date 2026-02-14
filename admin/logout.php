<?php
require_once __DIR__ . '/config.php';

$lang = current_lang();
session_destroy();
header('Location: ' . lang_url('login.php', $lang));
exit;
?>
