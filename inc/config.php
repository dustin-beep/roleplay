<?php
declare(strict_types=1);

return [
  'db' => [
    'dsn'  => 'mysql:host=localhost;dbname=bwk;charset=utf8mb4',
    'user' => 'root',
    'pass' => '',
  ],
  'storage_dir' => __DIR__ . '/../storage',
  'docs_dir'    => __DIR__ . '/../storage/docs',
  'logo_path'   => __DIR__ . '/../assets/img/crest.png',  // dein BWK-Logo
];
