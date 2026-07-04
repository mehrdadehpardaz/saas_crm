<?php
// api/industries.php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/database.php';

$pdo = getDB();
$action = $_GET['action'] ?? '';

if ($action === 'search') {
    $search = trim($_GET['search'] ?? '');
    if (mb_strlen($search) < 1) {
        echo json_encode([]);
        exit;
    }
    $stmt = $pdo->prepare("SELECT id, title FROM industries WHERE title LIKE ? ORDER BY title LIMIT 8");
    $stmt->execute(["%$search%"]);
    echo json_encode($stmt->fetchAll());

} elseif ($action === 'add') {
    $title = trim($_GET['title'] ?? '');
    if ($title === '') {
        echo json_encode(['error' => 'عنوان الزامی است']);
        exit;
    }
    // چک تکراری نبودن دقیق
    $stmt = $pdo->prepare("SELECT id FROM industries WHERE title = ?");
    $stmt->execute([$title]);
    if ($row = $stmt->fetch()) {
        echo json_encode(['id' => $row['id'], 'existed' => true]);
    } else {
        $stmt = $pdo->prepare("INSERT INTO industries (title) VALUES (?)");
        $stmt->execute([$title]);
        echo json_encode(['id' => $pdo->lastInsertId(), 'existed' => false]);
    }
} else {
    echo json_encode(['error' => 'Invalid action']);
}