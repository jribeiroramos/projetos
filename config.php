<?php
return [
  'db' => [
    'host'    => getenv('DB_HOST') ?: '127.0.0.1',
    'name'    => getenv('DB_NAME') ?: 'projetos_app',
    'user'    => getenv('DB_USER') ?: 'projetos',
    'pass'    => getenv('DB_PASS') ?: 'projetos',
    'charset' => 'utf8mb4',
  ],
  'app' => [
    'base_url'     => getenv('APP_BASE_URL') ?: '/projetos/',
    'session_name' => 'projapp_sess',
    'locale'       => 'pt_BR',
    'timezone'     => 'America/Sao_Paulo',
  ],
];
