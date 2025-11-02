<?php
/**
 * ProAgenda - Configurações do Sistema
 * Arquivo de configuração principal
 * 
 * @version 1.0
 * @author Leandro de Paula
 */

// Definir diretório raiz
define('APP_ROOT', __DIR__);

// === BANCO DE DADOS ===
define('DB_HOST', 'localhost');
define('DB_NAME', 'proagenda');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');
define('DB_CHARSET', 'utf8mb4');
define('DB_PREFIX', 'pa_');

// === SISTEMA ===
define('NOME_SISTEMA', 'ProAgenda');
define('VERSAO_SISTEMA', '1.0.0');
define('BASE_URL', 'http://localhost/proagenda');

// === GOOGLE OAUTH ===
define('GOOGLE_CLIENT_ID', '');
define('GOOGLE_CLIENT_SECRET', '');

// === SEGURANÇA ===
define('CSRF_TOKEN_NAME', 'csrf_token');
define('SESSION_TIMEOUT', 3600);

// === TIMEZONE ===
date_default_timezone_set('America/Sao_Paulo');

// === VALIDAÇÕES ===
if (version_compare(PHP_VERSION, '8.0.0') < 0) {
    die('ProAgenda requer PHP 8.0+');
}

// Status de agendamento
define('STATUS_AGENDADO', 'agendado');
define('STATUS_CONFIRMADO', 'confirmado');
define('STATUS_CONCLUIDO', 'concluido');
define('STATUS_CANCELADO', 'cancelado');
?>