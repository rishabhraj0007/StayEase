<?php
session_start();
session_destroy();
header('Location: /student-accommodation/index.php');
exit;
?>
