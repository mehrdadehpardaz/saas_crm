<?php
// controllers/BackupController.php
if (!defined('CRM_APP')) { http_response_code(403); exit('Direct access denied'); }
// پاک کردن همه buffer های باز

ob_start();

$user      = crm_get_current_user();
$is_admin  = in_array($user['role'], ['super_admin', 'admin']);
$is_super  = ($user['role'] === 'super_admin');

if (!$is_admin) {
    crm_redirect('index.php?page=dashboard');
    exit;
}

$pdo    = getDB();
$action = $_GET['action'] ?? 'index';

// ─────────────────────────────────────────
//  اکسل — admin و super_admin
// ─────────────────────────────────────────
// export_excel: فقط admin+
if ($action === 'export_excel' && !$is_admin) {
    http_response_code(403);
    die('دسترسی ندارید.');
}

elseif ($action === 'export_excel') {
    _backup_export_excel($pdo, $user, $is_super);
    exit;
}

// ─────────────────────────────────────────
//  SQL dump — فقط super_admin
// ─────────────────────────────────────────
// export_sql و restore: فقط super_admin
if (in_array($action, ['export_sql', 'restore']) && !$is_super) {
    http_response_code(403);
    die('دسترسی ندارید.');
}

elseif ($action === 'export_sql' && $is_super) {
    _backup_export_sql();
    exit;
}


// ─────────────────────────────────────────
//  بازگردانی — فقط super_admin
// ─────────────────────────────────────────
if ($action === 'restore' && $is_super && $_SERVER['REQUEST_METHOD'] === 'POST') {
    crm_csrf_verify();
    $result = _backup_restore($pdo);
    $restore_msg   = $result['msg'];
    $restore_ok    = $result['ok'];
    $restore_error = $result['error'] ?? '';
}

// ─────────────────────────────────────────
//  صفحه اصلی
// ─────────────────────────────────────────
$db_stats = _backup_db_stats($pdo, $user, $is_super);

include __DIR__ . '/../Views/backup/index.php';
ob_end_flush();


// ══════════════════════════════════════════════════════════════
//  توابع کمکی
// ══════════════════════════════════════════════════════════════

function _backup_export_excel(PDO $pdo, array $user, bool $is_super): void
{
    // ── تعیین محدوده داده ──
    $company = $user['company_name'];

    // تابع کمکی: فیلتر شرکت یا همه
    $where_users = $is_super
        ? "1=1"
        : "company_name = " . $pdo->quote($company);

    // دریافت user_ids در محدوده
    $uid_rows = $pdo->query("SELECT id FROM users WHERE $where_users")->fetchAll(PDO::FETCH_COLUMN);
    if (empty($uid_rows)) {
        die('داده‌ای یافت نشد.');
    }
    $uid_list = implode(',', array_map('intval', $uid_rows));

    // ── جمع‌آوری شیت‌ها ──
    $sheets = [];

    // 1. کاربران
    $sheets['کاربران'] = $pdo->query(
        "SELECT id, full_name, mobile, role, company_name, parent_id,
                plan_type, plan_expiry, created_at
         FROM users WHERE $where_users ORDER BY id"
    )->fetchAll(PDO::FETCH_ASSOC);

    // 2. مشتریان
    $sheets['مشتریان'] = $pdo->query(
        "SELECT c.id, c.company_name, c.contact_person, c.phone, c.email,
                c.notes, c.status, i.title AS industry, u.full_name AS owner, c.created_at
        FROM customers c
        LEFT JOIN industries i ON c.industry_id = i.id
        LEFT JOIN users u ON c.user_id = u.id
        WHERE c.user_id IN ($uid_list) ORDER BY c.id"
    )->fetchAll(PDO::FETCH_ASSOC);

    // 3. مخاطبین
    $sheets['مخاطبین'] = $pdo->query(
        "SELECT co.id, co.full_name, co.position, co.phone, co.email,
                co.is_primary, co.status, cu.company_name AS customer, co.created_at
        FROM contacts co
        LEFT JOIN customers cu ON co.customer_id = cu.id
        WHERE cu.user_id IN ($uid_list) ORDER BY co.id"
    )->fetchAll(PDO::FETCH_ASSOC);

    // 4. فعالیت‌ها
    $sheets['فعالیت‌ها'] = $pdo->query(
        "SELECT a.id, a.type, a.description,
                cu.company_name AS customer, u.full_name AS owner, a.created_at
        FROM activities a
        LEFT JOIN customers cu ON a.customer_id = cu.id
        LEFT JOIN users u ON a.user_id = u.id
        WHERE a.user_id IN ($uid_list) ORDER BY a.id"
    )->fetchAll(PDO::FETCH_ASSOC);

    // 5. تسک‌ها
    $sheets['تسک‌ها'] = $pdo->query(
        "SELECT t.id, t.title, t.status, t.next_followup_date,
                t.next_followup_topic, cu.company_name AS customer,
                u.full_name AS owner, t.created_at
        FROM tasks t
        LEFT JOIN users u ON t.user_id = u.id
        LEFT JOIN customers cu ON t.customer_id = cu.id
        WHERE t.user_id IN ($uid_list) ORDER BY t.id"
    )->fetchAll(PDO::FETCH_ASSOC);

    // 6. صنایع
    $sheets['صنایع'] = $pdo->query(
        "SELECT id, title, created_at FROM industries ORDER BY title"
    )->fetchAll(PDO::FETCH_ASSOC);

    // ── ساخت فایل اکسل با PHP순수 (بدون کتابخانه خارجی) ──
    _write_xlsx($sheets, $is_super ? 'backup_full' : 'backup_' . preg_replace('/[^a-z0-9]/i', '_', $company));
}


function _write_xlsx(array $sheets, string $filename): void
{
    // استفاده از SpreadsheetWriter ساده بر پایه XML/ZIP
    // چون سرور ممکنه PhpSpreadsheet نداشته باشه، از OpenDocument XML استفاده می‌کنیم
    // که اکسل آن را مستقیم باز می‌کند

    $colors = [
        'header_bg'   => 'FF1a73e8',
        'header_fg'   => 'FFFFFFFF',
        'alt_row'     => 'FFF0F4FF',
        'border'      => 'FFB0C4DE',
        'sheet_colors'=> ['FF1a73e8','FF34a853','FFf5a623','FF9c27b0','FF00897b','FFea4335'],
    ];

    // ── ساختار zip (xlsx = zip) ──
    $tmp = tempnam(sys_get_temp_dir(), 'crm_backup_') . '.xlsx';

    $zip = new ZipArchive();
    if ($zip->open($tmp, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        die('خطا در ساخت فایل.');
    }

    // shared strings
    $all_strings = [];
    $str_index   = [];

    $sheet_xmls  = [];
    $sheet_names = [];
    $si          = 0; // sheet index

    foreach ($sheets as $sheet_name => $rows) {
        $sheet_names[] = $sheet_name;
        $col_color = $colors['sheet_colors'][$si % count($colors['sheet_colors'])];
        $si++;

        if (empty($rows)) {
            $sheet_xmls[] = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
                . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
                . '<sheetData><row r="1"><c r="A1" t="s"><v>' . _xlsx_str('داده‌ای وجود ندارد', $all_strings, $str_index) . '</v></c></row></sheetData></worksheet>';
            continue;
        }

        $headers = array_keys($rows[0]);
        $xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
        $xml .= '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
              . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';

        // freeze top row
        $xml .= '<sheetViews><sheetView workbookViewId="0"><pane ySplit="1" topLeftCell="A2" activePane="bottomLeft" state="frozen"/></sheetView></sheetViews>';

        // column widths
        $xml .= '<cols>';
        foreach ($headers as $ci => $h) {
            $w = max(12, min(40, strlen($h) * 2 + 4));
            $xml .= '<col min="' . ($ci+1) . '" max="' . ($ci+1) . '" width="' . $w . '" customWidth="1"/>';
        }
        $xml .= '</cols>';

        $xml .= '<sheetData>';

        // header row
        $xml .= '<row r="1" ht="18" customHeight="1">';
        foreach ($headers as $ci => $h) {
            $col_letter = _xlsx_col($ci);
            $xml .= '<c r="' . $col_letter . '1" t="s" s="1"><v>' . _xlsx_str($h, $all_strings, $str_index) . '</v></c>';
        }
        $xml .= '</row>';

        // data rows
        foreach ($rows as $ri => $row) {
            $excel_row = $ri + 2;
            $style     = ($ri % 2 === 1) ? ' s="2"' : '';
            $xml .= '<row r="' . $excel_row . '"' . $style . '>';
            $vi = 0;
            foreach ($row as $cell) {
                $col_letter = _xlsx_col($vi);
                $cell_ref   = $col_letter . $excel_row;
                if (is_null($cell)) {
                    $xml .= '<c r="' . $cell_ref . '" s="' . ($ri%2===1?'3':'0') . '"/>';
                } elseif (is_numeric($cell) && !preg_match('/^0\d/', $cell)) {
                    $xml .= '<c r="' . $cell_ref . '" s="' . ($ri%2===1?'3':'0') . '"><v>' . htmlspecialchars((string)$cell) . '</v></c>';
                } else {
                    $xml .= '<c r="' . $cell_ref . '" t="s" s="' . ($ri%2===1?'3':'0') . '"><v>' . _xlsx_str((string)$cell, $all_strings, $str_index) . '</v></c>';
                }
                $vi++;
            }
            $xml .= '</row>';
        }

        $xml .= '</sheetData>';

        // auto filter
        $last_col = _xlsx_col(count($headers) - 1);
        $last_row = count($rows) + 1;
        $xml .= '<autoFilter ref="A1:' . $last_col . $last_row . '"/>';

        $xml .= '</worksheet>';
        $sheet_xmls[] = $xml;
    }

    // shared strings XML
    $ss_xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $ss_xml .= '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="' . count($all_strings) . '" uniqueCount="' . count($all_strings) . '">';
    foreach ($all_strings as $s) {
        $ss_xml .= '<si><t xml:space="preserve">' . htmlspecialchars($s) . '</t></si>';
    }
    $ss_xml .= '</sst>';

    // styles XML
    $styles_xml = _xlsx_styles($colors['header_bg'], $colors['header_fg'], $colors['alt_row']);

    // workbook XML
    $wb_xml  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $wb_xml .= '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
             . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">';
    $wb_xml .= '<bookViews><workbookView activeTab="0"/></bookViews>';
    $wb_xml .= '<sheets>';
    foreach ($sheet_names as $idx => $sn) {
        $wb_xml .= '<sheet name="' . htmlspecialchars($sn) . '" sheetId="' . ($idx+1) . '" r:id="rId' . ($idx+1) . '"/>';
    }
    $wb_xml .= '</sheets></workbook>';

    // workbook rels
    $wb_rels  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $wb_rels .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
    foreach ($sheet_names as $idx => $sn) {
        $wb_rels .= '<Relationship Id="rId' . ($idx+1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet' . ($idx+1) . '.xml"/>';
    }
    $wb_rels .= '<Relationship Id="rId' . (count($sheet_names)+1) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>';
    $wb_rels .= '<Relationship Id="rId' . (count($sheet_names)+2) . '" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';
    $wb_rels .= '</Relationships>';

    // [Content_Types]
    $ct  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $ct .= '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">';
    $ct .= '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>';
    $ct .= '<Default Extension="xml" ContentType="application/xml"/>';
    $ct .= '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>';
    $ct .= '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>';
    $ct .= '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>';
    foreach ($sheet_names as $idx => $sn) {
        $ct .= '<Override PartName="/xl/worksheets/sheet' . ($idx+1) . '.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
    }
    $ct .= '</Types>';

    // _rels/.rels
    $root_rels  = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>';
    $root_rels .= '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">';
    $root_rels .= '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>';
    $root_rels .= '</Relationships>';

    // ── افزودن فایل‌ها به zip ──
    $zip->addFromString('[Content_Types].xml', $ct);
    $zip->addFromString('_rels/.rels', $root_rels);
    $zip->addFromString('xl/workbook.xml', $wb_xml);
    $zip->addFromString('xl/_rels/workbook.xml.rels', $wb_rels);
    $zip->addFromString('xl/sharedStrings.xml', $ss_xml);
    $zip->addFromString('xl/styles.xml', $styles_xml);

    foreach ($sheet_xmls as $idx => $sx) {
        $zip->addFromString('xl/worksheets/sheet' . ($idx+1) . '.xml', $sx);
    }

    $zip->close();

    // ── ارسال به مرورگر ──
    $dl_name = $filename . '_' . date('Ymd_His') . '.xlsx';
    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="' . $dl_name . '"');
    header('Content-Length: ' . filesize($tmp));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    readfile($tmp);
    unlink($tmp);
    exit;
}

/** افزودن رشته به shared strings و برگشت index */
function _xlsx_str(string $s, array &$all, array &$idx): int
{
    if (!isset($idx[$s])) {
        $idx[$s]  = count($all);
        $all[]    = $s;
    }
    return $idx[$s];
}

/** تبدیل index ستون (0-based) به حرف اکسل */
function _xlsx_col(int $n): string
{
    $r = '';
    do {
        $r  = chr(65 + ($n % 26)) . $r;
        $n  = intdiv($n, 26) - 1;
    } while ($n >= 0);
    return $r;
}

/** ساخت styles.xml با رنگ هدر و ردیف‌های یک‌درمیان */
function _xlsx_styles(string $hbg, string $hfg, string $alt): string
{
    return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="3">
    <font><sz val="10"/><name val="Arial"/></font>
    <font><b/><sz val="10"/><color rgb="' . $hfg . '"/><name val="Arial"/></font>
    <font><sz val="10"/><name val="Arial"/></font>
  </fonts>
  <fills count="4">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="' . $hbg . '"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="' . $alt . '"/></patternFill></fill>
  </fills>
  <borders count="2">
    <border><left/><right/><top/><bottom/><diagonal/></border>
    <border>
      <left style="thin"><color rgb="FFB0C4DE"/></left>
      <right style="thin"><color rgb="FFB0C4DE"/></right>
      <top style="thin"><color rgb="FFB0C4DE"/></top>
      <bottom style="thin"><color rgb="FFB0C4DE"/></bottom>
    </border>
  </borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="4">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="1" xfId="0"><alignment horizontal="right" vertical="center" readingOrder="2"/></xf>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="1" xfId="0"><alignment horizontal="center" vertical="center" wrapText="1" readingOrder="2"/></xf>
    <xf numFmtId="0" fontId="0" fillId="3" borderId="1" xfId="0"><alignment horizontal="right" vertical="center" readingOrder="2"/></xf>
    <xf numFmtId="0" fontId="2" fillId="3" borderId="1" xfId="0"><alignment horizontal="right" vertical="center" readingOrder="2"/></xf>
  </cellXfs>
</styleSheet>';
}


// ── SQL Dump ──────────────────────────────────────────────────
function _backup_export_sql(): void
{
    $cfg = getDB();
    // database.php باید یک آرایه برگردونه یا متغیرهای global داشته باشه
    // پیاده‌سازی با mysqldump
    $host = defined('DB_HOST') ? DB_HOST : ($cfg['host'] ?? 'localhost');
    $name = defined('DB_NAME') ? DB_NAME : ($cfg['name'] ?? 'crm');
    $usr  = defined('DB_USER') ? DB_USER : ($cfg['user'] ?? 'root');
    $pass = defined('DB_PASS') ? DB_PASS : ($cfg['pass'] ?? '');

    $tmp  = tempnam(sys_get_temp_dir(), 'crm_sql_') . '.sql';
    $dl   = 'crm_backup_' . date('Ymd_His') . '.sql';

    $cmd  = sprintf(
        'mysqldump --single-transaction --routines --triggers -h %s -u %s %s %s > %s 2>&1',
        escapeshellarg($host),
        escapeshellarg($usr),
        $pass !== '' ? '-p' . escapeshellarg($pass) : '',
        escapeshellarg($name),
        escapeshellarg($tmp)
    );

    exec($cmd, $out, $ret);

    if ($ret !== 0 || !file_exists($tmp) || filesize($tmp) < 10) {
        // اگر mysqldump نبود از PDO dump کنیم
        _backup_pdo_dump($tmp);
    }

    while (ob_get_level() > 0) { ob_end_clean(); }
    header('Content-Type: application/octet-stream');
    header('Content-Disposition: attachment; filename="' . $dl . '"');
    header('Content-Length: ' . filesize($tmp));
    header('Cache-Control: no-cache, must-revalidate');
    header('Pragma: no-cache');
    header('Expires: 0');
    readfile($tmp);
    unlink($tmp);
    exit;
}

/** dump با PDO وقتی mysqldump در دسترس نیست */
function _backup_pdo_dump(string $out_file): void
{
    $pdo = getDB();
    $fp  = fopen($out_file, 'w');

    fwrite($fp, "-- CRM Database Backup\n");
    fwrite($fp, "-- Generated: " . date('Y-m-d H:i:s') . "\n");
    fwrite($fp, "-- -----------------------------------------------\n\n");
    fwrite($fp, "SET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE='NO_AUTO_VALUE_ON_ZERO';\n\n");

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        // ساختار جدول
        $create = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
        $create_sql = $create['Create Table'] ?? '';
        fwrite($fp, "DROP TABLE IF EXISTS `$table`;\n");
        fwrite($fp, $create_sql . ";\n\n");

        // داده‌ها
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $cols = '`' . implode('`, `', array_keys($rows[0])) . '`';
            $chunk = [];
            foreach ($rows as $row) {
                $vals = array_map(function($v) use ($pdo) {
                    if (is_null($v)) return 'NULL';
                    return $pdo->quote($v);
                }, array_values($row));
                $chunk[] = '(' . implode(', ', $vals) . ')';

                if (count($chunk) >= 50) {
                    fwrite($fp, "INSERT INTO `$table` ($cols) VALUES\n" . implode(",\n", $chunk) . ";\n");
                    $chunk = [];
                }
            }
            if ($chunk) {
                fwrite($fp, "INSERT INTO `$table` ($cols) VALUES\n" . implode(",\n", $chunk) . ";\n");
            }
            fwrite($fp, "\n");
        }
    }

    fwrite($fp, "SET FOREIGN_KEY_CHECKS=1;\n");
    fclose($fp);
}


// ── Restore ──────────────────────────────────────────────────
function _backup_restore(PDO $pdo): array
{
    if (empty($_FILES['sql_file']) || $_FILES['sql_file']['error'] !== UPLOAD_ERR_OK) {
        return ['ok' => false, 'msg' => 'فایلی آپلود نشد.', 'error' => 'upload_error'];
    }

    $file = $_FILES['sql_file'];
    $ext  = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    if ($ext !== 'sql') {
        return ['ok' => false, 'msg' => 'فقط فایل .sql مجاز است.'];
    }

    if ($file['size'] > 50 * 1024 * 1024) { // 50MB max
        return ['ok' => false, 'msg' => 'حجم فایل بیش از ۵۰ مگابایت است.'];
    }

    // خواندن و اجرای SQL
    $sql = file_get_contents($file['tmp_name']);
    if (!$sql) {
        return ['ok' => false, 'msg' => 'فایل خالی یا غیرقابل خواندن است.'];
    }

    // بررسی امنیت: فقط DDL/DML مجاز باشه
    $forbidden = [
        'LOAD_FILE', 'INTO OUTFILE', 'INTO DUMPFILE',
        'DROP DATABASE', 'GRANT ', 'REVOKE ', 'CREATE USER',
        'ALTER USER', 'SET GLOBAL', 'LOAD DATA', 'SYSTEM(',
        'xp_cmdshell', '<?php', '<?='
    ];
    foreach ($forbidden as $f) {
        if (stripos($sql, $f) !== false) {
            return ['ok' => false, 'msg' => 'محتوای فایل مجاز نیست.'];
        }
    }

    try {
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // تقسیم به statement‌های جداگانه
        $statements = array_filter(
            array_map('trim', _split_sql($sql)),
            fn($s) => $s !== '' && !str_starts_with($s, '--')
        );

        $pdo->exec('SET FOREIGN_KEY_CHECKS=0');
        $count = 0;
        foreach ($statements as $stmt) {
            if (trim($stmt) === '') continue;
            $pdo->exec($stmt);
            $count++;
        }
        $pdo->exec('SET FOREIGN_KEY_CHECKS=1');

        return ['ok' => true, 'msg' => "بازگردانی موفق — {$count} دستور اجرا شد."];
    } catch (PDOException $e) {
        return ['ok' => false, 'msg' => 'خطا در اجرا: ' . $e->getMessage()];
    }
}

/** تقسیم SQL به statement‌های جداگانه (با در نظر گرفتن strings) */
function _split_sql(string $sql): array
{
    $statements = [];
    $current    = '';
    $in_string  = false;
    $string_char = '';
    $len = strlen($sql);

    for ($i = 0; $i < $len; $i++) {
        $char = $sql[$i];

        if ($in_string) {
            $current .= $char;
            if ($char === '\\') {
                $current .= $sql[++$i] ?? '';
            } elseif ($char === $string_char) {
                $in_string = false;
            }
        } elseif ($char === "'" || $char === '"' || $char === '`') {
            $in_string   = true;
            $string_char = $char;
            $current    .= $char;
        } elseif ($char === ';') {
            $statements[] = trim($current);
            $current      = '';
        } elseif ($char === '-' && ($sql[$i+1] ?? '') === '-') {
            // skip comment line
            while ($i < $len && $sql[$i] !== "\n") $i++;
        } elseif ($char === '/' && ($sql[$i+1] ?? '') === '*') {
            // skip block comment
            $i += 2;
            while ($i < $len - 1 && !($sql[$i] === '*' && $sql[$i+1] === '/')) $i++;
            $i += 2;
        } else {
            $current .= $char;
        }
    }
    if (trim($current) !== '') $statements[] = trim($current);
    return $statements;
}


// ── آمار دیتابیس ─────────────────────────────────────────────
function _backup_db_stats(PDO $pdo, array $user, bool $is_super): array
{
    $where = $is_super ? "1=1" : "company_name = " . $pdo->quote($user['company_name']);
    $uid_rows = $pdo->query("SELECT id FROM users WHERE $where")->fetchAll(PDO::FETCH_COLUMN);
    $uid_list = empty($uid_rows) ? '0' : implode(',', array_map('intval', $uid_rows));

    return [
        'users'      => $pdo->query("SELECT COUNT(*) FROM users WHERE $where")->fetchColumn(),
        'customers'  => $pdo->query("SELECT COUNT(*) FROM customers WHERE user_id IN ($uid_list)")->fetchColumn(),
        'contacts'   => $pdo->query("SELECT COUNT(*) FROM contacts WHERE customer_id IN (SELECT id FROM customers WHERE user_id IN ($uid_list))")->fetchColumn(),
        'activities' => $pdo->query("SELECT COUNT(*) FROM activities WHERE user_id IN ($uid_list)")->fetchColumn(),
        'tasks'      => $pdo->query("SELECT COUNT(*) FROM tasks WHERE user_id IN ($uid_list)")->fetchColumn(),
    ];
}