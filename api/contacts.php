<?php
// api/contacts.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/helpers.php';

$customer_id = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : 0;

if ($customer_id > 0) {
    $pdo = getDB();
    $stmt = $pdo->prepare("SELECT * FROM contacts WHERE customer_id = ? AND status = 'active' ORDER BY is_primary DESC, created_at ASC");
    $stmt->execute([$customer_id]);
    $contacts = $stmt->fetchAll();
    echo json_encode($contacts);
} else {
    echo json_encode([]);
}