<?php
declare(strict_types=1);

// Bootstrap sessions and config
session_start([
  'cookie_httponly' => true,
  'cookie_samesite' => 'Lax',
]);

// Serve SPA shell
?><!doctype html>
<html lang="en">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Photo Collage</title>
    <link rel="icon" href="data:," />
    <script type="module" src="/assets/index.js" defer></script>
    <link rel="stylesheet" href="/assets/style.css" />
  </head>
  <body>
    <div id="app"></div>
    <noscript>This application requires JavaScript to run.</noscript>
  </body>
  </html>


