<?php
// Simple entry for /api/* to reuse root api.php front-controller
// Place this file at /api/index.php so requests to /api/<endpoint> hit api.php

// Adjust SCRIPT_NAME and REQUEST_URI if needed; simply include the central api.php
require_once __DIR__ . '/../api.php';

?>
