<?php
require_once 'config.php';

// Caminho do banco SQLite
define('SQLITE_PATH', __DIR__ . '/../fatecconnect.sqlite');

// Função para estabelecer conexão com o banco de dados SQLite
function getConnection() {
    $conn = new SQLite3(SQLITE_PATH);
    $conn->exec('PRAGMA foreign_keys = ON;');
    return $conn;
}

// Função para executar consultas (SELECT)
function query($sql, $params = []) {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);

    foreach ($params as $key => $param) {
        $type = SQLITE3_TEXT;
        if (is_int($param)) $type = SQLITE3_INTEGER;
        elseif (is_float($param)) $type = SQLITE3_FLOAT;
        $stmt->bindValue($key + 1, $param, $type);
    }

    $result = $stmt->execute();
    $rows = [];
    while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
        $rows[] = $row;
    }
    $stmt->close();
    $conn->close();

    return $rows;
}

// Função para obter um único registro
function fetchOne($sql, $params = []) {
    $result = query($sql, $params);
    return $result[0] ?? null;
}

// Função para obter vários registros
function fetchAll($sql, $params = []) {
    return query($sql, $params);
}

// Função para executar operações sem retorno (INSERT, UPDATE, DELETE)
function execute($sql, $params = []) {
    $conn = getConnection();
    $stmt = $conn->prepare($sql);

    foreach ($params as $key => $param) {
        $type = SQLITE3_TEXT;
        if (is_int($param)) $type = SQLITE3_INTEGER;
        elseif (is_float($param)) $type = SQLITE3_FLOAT;
        $stmt->bindValue($key + 1, $param, $type);
    }

    $result = $stmt->execute();
    $insertId = $conn->lastInsertRowID();
    $affectedRows = $conn->changes();

    $stmt->close();
    $conn->close();

    return [
        'success' => $result ? true : false,
        'insert_id' => $insertId,
        'affected_rows' => $affectedRows
    ];
}

// Script para criar o banco de dados e tabelas (descomentar para executar a primeira vez)

$conn = getConnection();

// Criar tabela de usuários
$sql = "CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL,
    email TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    user_type INTEGER NOT NULL,
    profile_picture TEXT DEFAULT NULL,
    bio TEXT,
    registration_date DATETIME DEFAULT CURRENT_TIMESTAMP,
    last_login DATETIME DEFAULT NULL
)";
$conn->exec($sql);

// Criar tabela de posts
$sql = "CREATE TABLE IF NOT EXISTS posts (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    title TEXT NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
$conn->exec($sql);

// Criar tabela de comentários
$sql = "CREATE TABLE IF NOT EXISTS comments (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    post_id INTEGER NOT NULL,
    user_id INTEGER NOT NULL,
    content TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
)";
$conn->exec($sql);

// Inserir administrador padrão
$admin_password = password_hash('Inot1234$', PASSWORD_DEFAULT);
$sql = "INSERT OR IGNORE INTO users (name, email, password, user_type) 
        VALUES ('Administrador', 'admin@fatec.sp.gov.br', '$admin_password', 4)";
$conn->exec($sql);

$conn->close();
?>