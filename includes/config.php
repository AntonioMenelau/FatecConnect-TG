<?php
// Configurações do banco de dados
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'fatecconnect');

// Configurações da aplicação
define('SITE_NAME', 'FatecConnect');
define('SITE_URL', 'http://localhost/FatecConnect/');

// Tipos de usuário
define('USER_STUDENT', 1);
define('USER_ALUMNI', 2);
define('USER_PROFESSOR', 3);
define('USER_ADMIN', 4);

// Configurações de sessão
session_start();


// imports de todos os componentes
require_once __DIR__ . '/../componentes/db.php';  
require_once __DIR__ . '/../componentes/usuario.php';
require_once __DIR__ . '/../componentes/post.php';
require_once __DIR__ . '/../componentes/comentario.php';
require_once __DIR__ . '/../componentes/functions.php';
