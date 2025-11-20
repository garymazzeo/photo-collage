<?php
// Fallback redirect if .htaccess isn't working
// Redirects root requests to /public/ directory
header('Location: /public/', true, 301);
exit;

