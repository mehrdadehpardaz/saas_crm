<?php
// models/Task.php

class Task {
    
    public static function getAll($customer_id, $user_id, $is_manager = false) {
        $pdo = getDB();

        // نمایش «این‌که تسکی هست» برای همه‌ی اعضای سازمان آزاده (چون خودِ
        // مشتری company-wide قابل مشاهده‌ست) — ولی دسترسی کامل به جزئیات
        // هر تسک را crm_user_can_access_owned_record() در لایه‌ی نمایش
        // (per-row) مشخص می‌کند، نه این کوئری.
        $sql = "SELECT t.*, u.full_name as agent_name, comp.name as company_label
                FROM tasks t
                JOIN users u ON t.user_id = u.id
                LEFT JOIN companies comp ON t.company_id = comp.id
                WHERE t.customer_id = ?
                ORDER BY t.created_at DESC";

        $stmt = $pdo->prepare($sql);
        $stmt->execute([$customer_id]);
        return $stmt->fetchAll();
    }

    public static function getById($id, $user_id = null, $is_manager = false) {
        $pdo = getDB();
        
        $sql = "SELECT t.*, c.company_name, u.full_name as agent_name, comp.name as company_label
                FROM tasks t
                JOIN customers c ON t.customer_id = c.id
                JOIN users u ON t.user_id = u.id
                LEFT JOIN companies comp ON t.company_id = comp.id
                WHERE t.id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    public static function create($data) {
        $pdo = getDB();
        $stmt = $pdo->prepare("INSERT INTO tasks (user_id, customer_id, company_id, title, next_followup_date, next_followup_topic) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['user_id'],
            $data['customer_id'],
            $data['company_id'] ?? null,
            $data['title'],
            $data['next_followup_date'] ?? null,
            $data['next_followup_topic'] ?? null
        ]);
        return $pdo->lastInsertId();
    }
    
    public static function update($id, $data) {
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE tasks SET 
                               title = ?, status = ?, next_followup_date = ?, next_followup_topic = ?
                               WHERE id = ?");
        return $stmt->execute([
            $data['title'],
            $data['status'] ?? 'active',
            $data['next_followup_date'] ?? null,
            $data['next_followup_topic'] ?? null,
            $id
        ]);
    }
    
    public static function delete($id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("DELETE FROM tasks WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    public static function getActivities($task_id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT a.*, co.full_name as contact_name, u.full_name as agent_name
                               FROM activities a
                               LEFT JOIN contacts co ON a.contact_id = co.id
                               JOIN users u ON a.user_id = u.id
                               WHERE a.task_id = ?
                               ORDER BY a.created_at DESC");
        $stmt->execute([$task_id]);
        return $stmt->fetchAll();
    }
    
    // گرفتن همه ریمایندرهای فعال (برای داشبورد)
    public static function getActiveReminders($user_id, $is_manager = false) {
        $pdo = getDB();
        
        if ($is_manager) {
            $sql = "SELECT t.*, c.company_name, u.full_name as agent_name
                    FROM tasks t
                    JOIN customers c ON t.customer_id = c.id
                    JOIN users u ON t.user_id = u.id
                    WHERE t.status = 'active'
                    AND t.next_followup_date IS NOT NULL
                    AND t.user_id IN (SELECT id FROM users WHERE id = ? OR parent_id = ?)
                    ORDER BY t.next_followup_date ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $user_id]);
        } else {
            $sql = "SELECT t.*, c.company_name, u.full_name as agent_name
                    FROM tasks t
                    JOIN customers c ON t.customer_id = c.id
                    JOIN users u ON t.user_id = u.id
                    WHERE t.status = 'active'
                    AND t.next_followup_date IS NOT NULL
                    AND t.user_id = ?
                    ORDER BY t.next_followup_date ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
        }
        
        return $stmt->fetchAll();
    }
    
    public static function getTodayReminders($user_id, $is_manager = false) {
        $pdo = getDB();
        
        if ($is_manager) {
            $sql = "SELECT t.*, c.company_name, u.full_name as agent_name
                    FROM tasks t
                    JOIN customers c ON t.customer_id = c.id
                    JOIN users u ON t.user_id = u.id
                    WHERE t.status = 'active'
                    AND DATE(t.next_followup_date) = CURDATE()
                    AND t.user_id IN (SELECT id FROM users WHERE id = ? OR parent_id = ?)
                    ORDER BY t.next_followup_date ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id, $user_id]);
        } else {
            $sql = "SELECT t.*, c.company_name, u.full_name as agent_name
                    FROM tasks t
                    JOIN customers c ON t.customer_id = c.id
                    JOIN users u ON t.user_id = u.id
                    WHERE t.status = 'active'
                    AND DATE(t.next_followup_date) = CURDATE()
                    AND t.user_id = ?
                    ORDER BY t.next_followup_date ASC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$user_id]);
        }
        
        return $stmt->fetchAll();
    }

    
}