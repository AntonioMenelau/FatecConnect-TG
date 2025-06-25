<?php
require_once 'includes/config.php';

// Fazer logout
logoutUser();

// Redirecionar para a página inicial
redirect('index.php');