<?php
// test.php
session_start();

echo '<pre>';

// چک کن ببینیم get_current_user قبلاً تعریف شده؟
if (function_exists('get_current_user')) {
    echo "⚠️ get_current_user ALREADY EXISTS before include!\n";
    $ref = new ReflectionFunction('get_current_user');
    echo "Defined in: " . $ref->getFileName() . " on line " . $ref->getStartLine() . "\n\n";
} else {
    echo "✅ get_current_user NOT defined yet\n\n";
}

// حالا include کن
require_once 'config/database.php';
require_once 'includes/helpers.php';

echo "After include:\n";
echo "Function exists: " . (function_exists('get_current_user') ? 'YES' : 'NO') . "\n\n";

$user = get_current_user();
echo "get_current_user() returns:\n";
var_dump($user);
echo '</pre>';