<?php
// models/Plan.php

class Plan {
    
    public static function getAll() {
        $pdo = getDB();
        return $pdo->query("SELECT * FROM plans ORDER BY type, id")->fetchAll();
    }
    
    public static function getById($id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM plans WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public static function getByType($type) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM plans WHERE type = ? LIMIT 1");
        $stmt->execute([$type]);
        return $stmt->fetch();
    }
    
    /**
     * محاسبه قیمت کل
     * قیمت = قیمت پایه + (تعداد کاربر × قیمت هر کاربر)
     */
    public static function calculatePrice($user_count, $period = 'monthly') {
        $base = self::getByType('base');
        $per_user = self::getByType('per_user');
        
        $field = ($period === 'yearly') ? 'price_yearly' : 'price_monthly';
        
        $base_price = (int)$base[$field];
        $per_user_price = (int)$per_user[$field];
        
        return $base_price + ($user_count * $per_user_price);
    }
}