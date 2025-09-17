<?php
// public/musicremoval.php
// Visualizador público para a UI do modal (sem alterações de banco).
require_once __DIR__ . '/../app/Controllers/MusicRemovalController.php';
$controller = new \App\Controllers\MusicRemovalController();
$songs = $controller->getPendingSongs(); // dados mock genéricos
include __DIR__ . '/../app/Views/musicremoval.php';
