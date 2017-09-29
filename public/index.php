<?php
// change dir to project root
chdir('../');

include 'boot.php';

// dispatch the current route, taken from HTTP Request
$response = dispatchRoute($_SERVER['REQUEST_URI']);

echo $response;