<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

echo "PHP version: " . phpversion() . "<br>";
echo "Testing includes...<br>";

try {
    require_once __DIR__ . '/config/database.php';
    echo "✅ database.php loaded<br>";
} catch (Throwable $e) {
    echo "❌ database.php error: " . $e->getMessage() . "<br>";
}

try {
    require_once __DIR__ . '/includes/helpers.php';
    echo "✅ helpers.php loaded<br>";
} catch (Throwable $e) {
    echo "❌ helpers.php error: " . $e->getMessage() . "<br>";
}

try {
    session_start();
    echo "✅ session started<br>";
} catch (Throwable $e) {
    echo "❌ session error: " . $e->getMessage() . "<br>";
}

try {
    $pdo = getDB();
    echo "✅ DB connected<br>";
} catch (Throwable $e) {
    echo "❌ DB error: " . $e->getMessage() . "<br>";
}

echo "<br>Function checks:<br>";
echo function_exists('crm_csrf_token') ? "✅ crm_csrf_token exists<br>" : "❌ crm_csrf_token MISSING<br>";
echo function_exists('crm_is_logged_in') ? "✅ crm_is_logged_in exists<br>" : "❌ crm_is_logged_in MISSING<br>";
echo function_exists('jdate') ? "✅ jdate exists<br>" : "❌ jdate MISSING<br>";

try {
    include __DIR__ . '/Controllers/AuthController.php';
    echo "<br>✅ AuthController loaded without fatal error<br>";
} catch (Throwable $e) {
    echo "<br>❌ AuthController error: " . $e->getMessage() . " (line " . $e->getLine() . ")<br>";
}