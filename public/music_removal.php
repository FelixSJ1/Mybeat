<?php
require_once __DIR__ . '/../app/Controllers/music_removal_controller.php';

$controller = new Music_Removal_Controller();
$action = isset($_GET['action']) ? $_GET['action'] : 'index';

if ($action === 'confirm' && isset($_GET['id'])) {
    $controller->confirm((int)$_GET['id']);
    exit;
}

if ($action === 'delete') {
    $controller->delete();
    exit;
}

$controller->index();
