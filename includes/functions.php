<?php
/**
 * ProAgenda - Funções Globais
 * 
 * @version 1.0
 * @author Leandro de Paula
 */

if (!defined('APP_ROOT')) {
    die('Acesso direto não permitido');
}

// === SEGURANÇA ===
function sanitizar($string) {
    if (is_array($string)) {
        return array_map('sanitizar', $string);
    }
    return htmlspecialchars(trim($string), ENT_QUOTES | ENT_HTML5, 'UTF-8');
}

function gerar_csrf_token() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    
    return $_SESSION['csrf_token'];
}

function verificar_csrf_token($token) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// === AUTENTICAÇÃO ===
function usuario_logado() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['usuario_id']) && !empty($_SESSION['usuario_id']);
}

function admin_logado() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    return isset($_SESSION['admin_id']) && !empty($_SESSION['admin_id']);
}

function obter_usuario_atual() {
    if (!usuario_logado()) return null;
    
    return [
        'id' => $_SESSION['usuario_id'],
        'nome' => $_SESSION['usuario_nome'] ?? '',
        'email' => $_SESSION['usuario_email'] ?? '',
        'foto' => $_SESSION['usuario_foto'] ?? null
    ];
}

function obter_admin_atual() {
    if (!admin_logado()) return null;
    
    return [
        'id' => $_SESSION['admin_id'],
        'nome' => $_SESSION['admin_nome'] ?? '',
        'email' => $_SESSION['admin_email'] ?? '',
        'tipo' => $_SESSION['admin_tipo'] ?? 'admin'
    ];
}

// === REDIRECIONAMENTO ===
function redirect($url = '') {
    $destino = empty($url) ? BASE_URL : url($url);
    header('Location: ' . $destino);
    exit;
}

function url($path = '') {
    $path = ltrim($path, '/');
    return BASE_URL . ($path ? '/' . $path : '');
}

// === FLASH MESSAGES ===
function set_flash($tipo, $mensagem) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $_SESSION['flash'] = ['tipo' => $tipo, 'mensagem' => $mensagem];
}

function get_flash() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    $flash = $_SESSION['flash'] ?? null;
    unset($_SESSION['flash']);
    return $flash;
}

// === VALIDAÇÃO ===
function validar_email($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
}

function validar_telefone($telefone) {
    $telefone = preg_replace('/[^0-9]/', '', $telefone);
    return preg_match('/^[1-9]{2}9?[0-9]{8}$/', $telefone);
}

// === DATA/HORA ===
function data_br_para_mysql($data_br) {
    if (empty($data_br)) return null;
    
    $partes = explode('/', $data_br);
    if (count($partes) === 3) {
        return $partes[2] . '-' . str_pad($partes[1], 2, '0', STR_PAD_LEFT) . '-' . str_pad($partes[0], 2, '0', STR_PAD_LEFT);
    }
    
    return null;
}

function formatar_data_hora($data_hora, $formato = 'd/m/Y H:i') {
    if (empty($data_hora)) return '';
    
    $dt = new DateTime($data_hora);
    return $dt->format($formato);
}

// === LOG ===
function log_auditoria($acao, $tabela = '', $registro_id = 0, $dados_anteriores = '', $dados_novos = '') {
    try {
        $db = new Database();
        
        $dados_log = [
            'acao' => $acao,
            'tabela_afetada' => $tabela,
            'registro_id' => $registro_id,
            'usuario_id' => $_SESSION['usuario_id'] ?? null,
            'admin_id' => $_SESSION['admin_id'] ?? null,
            'ip_address' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            'dados_anteriores' => $dados_anteriores,
            'dados_novos' => $dados_novos
        ];
        
        $db->inserir('logs_auditoria', $dados_log);
        
    } catch (Exception $e) {
        error_log('Erro log auditoria: ' . $e->getMessage());
    }
}

// === UTILITÁRIAS ===
function gerar_slug($texto) {
    $texto = strtolower($texto);
    $texto = preg_replace('/[áàãâä]/', 'a', $texto);
    $texto = preg_replace('/[éèêë]/', 'e', $texto);
    $texto = preg_replace('/[íìîï]/', 'i', $texto);
    $texto = preg_replace('/[óòõôö]/', 'o', $texto);
    $texto = preg_replace('/[úùûü]/', 'u', $texto);
    $texto = preg_replace('/[ç]/', 'c', $texto);
    $texto = preg_replace('/[^a-z0-9\s]/', '', $texto);
    return preg_replace('/\s+/', '-', trim($texto));
}

function obter_configuracao($chave, $padrao = null) {
    try {
        $db = new Database();
        $config = $db->buscarUm('configuracoes', 'chave = ?', [$chave]);
        return $config ? $config['valor'] : $padrao;
    } catch (Exception $e) {
        return $padrao;
    }
}

// === SISTEMA ===
function sistema_instalado() {
    try {
        $db = new Database();
        $tabelas = $db->query("SHOW TABLES LIKE '" . DB_PREFIX . "usuarios'");
        return $tabelas->rowCount() > 0;
    } catch (Exception $e) {
        return false;
    }
}
?>