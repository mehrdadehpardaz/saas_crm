<?php
// models/Customer.php

class Customer {
    
    /**
     * گرفتن لیست مشتریان با دسترسی سلسله‌مراتبی
     */
    public static function getAll($user_id, $is_manager = false, $search = '') {
        $pdo = getDB();
        
        $root_id = crm_get_company_root($user_id);
        $member_ids = crm_get_company_members($root_id);
        
        if (empty($member_ids)) {
            return [];
        }
        
        $placeholders = implode(',', array_fill(0, count($member_ids), '?'));
        
        $sql = "SELECT c.*, i.title as industry_title, u.full_name as agent_name, comp.name as company_label,
                (SELECT COUNT(*) FROM activities WHERE customer_id = c.id) as activity_count
                FROM customers c
                LEFT JOIN industries i ON c.industry_id = i.id
                LEFT JOIN companies comp ON c.company_id = comp.id
                JOIN users u ON c.user_id = u.id
                WHERE c.status = 'active' 
                AND c.user_id IN ($placeholders)";
        
        $params = $member_ids;
        
        if (!empty($search)) {
            $sql .= " AND (c.company_name LIKE ? OR c.phone LIKE ? OR i.title LIKE ?)";
            $s = "%$search%";
            $params[] = $s;
            $params[] = $s;
            $params[] = $s;
        }
        
        $sql .= " ORDER BY c.id DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * گرفتن یک مشتری با ID
     */
    public static function getById($id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT c.*, i.title as industry_title, u.full_name as agent_name, comp.name as company_label
                            FROM customers c
                            LEFT JOIN industries i ON c.industry_id = i.id
                            LEFT JOIN companies comp ON c.company_id = comp.id
                            JOIN users u ON c.user_id = u.id
                            WHERE c.id = ? AND c.status = 'active'");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }
    
    /**
     * ایجاد مشتری جدید + ثبت خودکار مخاطب اصلی
     */
    public static function create($data) {
        $pdo = getDB();
        
        try {
            $pdo->beginTransaction();
            
            $stmt = $pdo->prepare("INSERT INTO customers (user_id, company_id, industry_id, company_name, contact_person, phone, email, notes) 
                                   VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([
                $data['user_id'],
                $data['company_id'] ?? null,
                $data['industry_id'] ?? null,
                $data['company_name'],
                $data['contact_person'] ?? null,
                $data['phone'] ?? null,
                $data['email'] ?? null,
                $data['notes'] ?? null
            ]);
            
            $customer_id = $pdo->lastInsertId();
            
            // ثبت خودکار مخاطب اصلی اگر contact_person پر باشد — با شماره
            // همراه اختصاصی خودش (contact_phone)، نه شماره تلفن شرکت
            if (!empty($data['contact_person'])) {
                self::upsertPrimaryContact(
                    $customer_id,
                    $data['contact_person'],
                    $data['contact_position'] ?? null,
                    $data['contact_phone'] ?? null,
                    $data['email'] ?? null
                );
            }
            
            $pdo->commit();
            return $customer_id;
        } catch (Exception $e) {
            $pdo->rollBack();
            return false;
        }
    }
    
    /**
     * آپدیت مشتری
     */
    public static function update($id, $data) {
        $pdo = getDB();
        $stmt = $pdo->prepare("UPDATE customers SET 
                               industry_id = ?, company_name = ?, contact_person = ?, 
                               phone = ?, email = ?, notes = ?
                               WHERE id = ?");
        $ok = $stmt->execute([
            $data['industry_id'] ?? null,
            $data['company_name'],
            $data['contact_person'] ?? null,
            $data['phone'] ?? null,
            $data['email'] ?? null,
            $data['notes'] ?? null,
            $id
        ]);

        // هماهنگ نگه‌داشتن مخاطب اصلی با فیلدهای «شخص اصلی» فرم مشتری
        if ($ok && !empty($data['contact_person'])) {
            self::upsertPrimaryContact(
                $id,
                $data['contact_person'],
                $data['contact_position'] ?? null,
                $data['contact_phone'] ?? null,
                $data['email'] ?? null
            );
        }

        return $ok;
    }

    /**
     * ایجاد یا بروزرسانی «مخاطب اصلی» (is_primary = 1) یک مشتری.
     * اگر مخاطب اصلی از قبل وجود داشته باشد، اطلاعاتش (از جمله شماره همراه)
     * بروزرسانی می‌شود؛ وگرنه یک مخاطب جدید با is_primary=1 ساخته می‌شود.
     * هیچ‌وقت مخاطب موجود را حذف نمی‌کند — فقط اضافه/بروزرسانی.
     */
    public static function upsertPrimaryContact($customer_id, $full_name, $position = null, $phone = null, $email = null) {
        $full_name = trim((string)$full_name);
        if ($full_name === '') {
            return;
        }

        $pdo = getDB();

        // سازمان (تنانت) مخاطب همیشه از روی مشتریِ صاحبش گرفته می‌شود —
        // نه از کاربر جاری — تا همیشه با سازمانِ واقعیِ آن مشتری یکی باشد.
        $stmt = $pdo->prepare("SELECT company_id FROM customers WHERE id = ?");
        $stmt->execute([$customer_id]);
        $company_id = $stmt->fetchColumn() ?: null;

        $stmt = $pdo->prepare("SELECT id FROM contacts WHERE customer_id = ? AND is_primary = 1 AND status = 'active' LIMIT 1");
        $stmt->execute([$customer_id]);
        $existing = $stmt->fetch();

        if ($existing) {
            $stmt = $pdo->prepare("UPDATE contacts SET full_name = ?, position = ?, phone = ?, email = ?, company_id = ? WHERE id = ?");
            $stmt->execute([$full_name, $position, $phone, $email, $company_id, $existing['id']]);
        } else {
            $stmt = $pdo->prepare("INSERT INTO contacts (customer_id, company_id, full_name, position, phone, email, is_primary, status) 
                                   VALUES (?, ?, ?, ?, ?, ?, 1, 'active')");
            $stmt->execute([$customer_id, $company_id, $full_name, $position, $phone, $email]);
        }
    }

    /**
     * گرفتن مخاطب اصلی یک مشتری (برای پیش‌پر کردن فرم ویرایش)
     */
    public static function getPrimaryContact($customer_id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM contacts WHERE customer_id = ? AND is_primary = 1 AND status = 'active' ORDER BY id ASC LIMIT 1");
        $stmt->execute([$customer_id]);
        return $stmt->fetch();
    }
    
    /**
     * حذف مشتری
     */
    public static function delete($id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
        return $stmt->execute([$id]);
    }
    
    /**
     * گرفتن مخاطبین یک مشتری
     */
    public static function getContacts($customer_id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM contacts WHERE customer_id = ? AND status = 'active' ORDER BY is_primary DESC");
        $stmt->execute([$customer_id]);
        return $stmt->fetchAll();
    }
    
    /**
     * گرفتن فعالیت‌های یک مشتری
     */
    public static function getActivities($customer_id, $limit = 10) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT a.*, co.full_name as contact_name, u.full_name as agent_name, t.title as task_title
                            FROM activities a
                            LEFT JOIN contacts co ON a.contact_id = co.id
                            JOIN users u ON a.user_id = u.id
                            LEFT JOIN tasks t ON a.task_id = t.id
                            WHERE a.customer_id = ?
                            ORDER BY a.created_at DESC
                            LIMIT ?");
        $stmt->execute([$customer_id, $limit]);
        return $stmt->fetchAll();
    }
    public static function getTasks($customer_id) {
        $pdo = getDB();
        $stmt = $pdo->prepare("SELECT * FROM tasks WHERE customer_id = ? ORDER BY created_at DESC");
        $stmt->execute([$customer_id]);
        return $stmt->fetchAll();
    }
}