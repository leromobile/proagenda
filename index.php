<?php
/**
 * ProAgenda - Roteamento Principal
 * 
 * @version 1.0
 * @author Leandro de Paula
 */

// Verificar instalação
if (!file_exists('config.php')) {
    header('Location: install/');
    exit;
}

require_once 'config.php';
require_once 'includes/functions.php';
require_once 'includes/database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obter rota
$rota = obter_rota_atual();

// Roteamento
switch ($rota) {
    case '':
    case '/':
        require_once 'public/index.php';
        break;
    
    case '/agendar':
        require_once 'public/agendar.php';
        break;
    
    case '/meus-agendamentos':
        require_once 'public/meus-agendamentos.php';
        break;
    
    case '/admin':
        require_once 'admin/index.php';
        break;
    
    case '/install':
        require_once 'install/index.php';
        break;
    
    default:
        http_response_code(404);
        echo 'Página não encontrada';
        break;
}

function obter_rota_atual() {
    $uri = $_SERVER['REQUEST_URI'] ?? '/';
    $path = parse_url($uri, PHP_URL_PATH);
    return '/' . trim($path, '/');
}
?>