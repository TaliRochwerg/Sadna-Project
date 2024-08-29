<?php
session_start();

if (isset($_SESSION['user'])) {
  header('Location:./certification.php');
} else {
    header('Location:./login.php');
}

?>