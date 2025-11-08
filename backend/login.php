<?php
// Redirect to frontend login page
// Hey Tracy I moved your authentication logic to login_handler.php for MVC separation

header('Location: ../frontend/login.php');
exit;
?>