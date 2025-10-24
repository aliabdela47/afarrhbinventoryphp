<?php
/**
 * Audit Logging System
 */

require_once __DIR__ . '/db.php';

/**
 * Log an action to the audit trail
 * 
 * @param string $action Description of action
 * @param string $affectedTable Table affected
 * @param int $affectedId ID of affected record
 * @param mixed $oldValue Old value (will be JSON encoded)
 * @param mixed $newValue New value (will be JSON encoded)
 */
function auditLog($action, $affectedTable = null, $affectedId = null, $oldValue = null, $newValue = null) {
    try {
        $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
        $ipAddress = getClientIp();
        
        $oldValueJson = $oldValue !== null ? json_encode($oldValue) : null;
        $newValueJson = $newValue !== null ? json_encode($newValue) : null;
        
        Database::query(
            "INSERT INTO AUDITLOG (userid, action, affectedtable, affectedid, oldvalue, newvalue, ipaddress) 
             VALUES (?, ?, ?, ?, ?, ?, ?)",
            [$userId, $action, $affectedTable, $affectedId, $oldValueJson, $newValueJson, $ipAddress]
        );
    } catch (Exception $e) {
        error_log("Audit Log Error: " . $e->getMessage());
    }
}

/**
 * Get client IP address
 */
function getClientIp() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
        return $_SERVER['HTTP_CLIENT_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        return $_SERVER['HTTP_X_FORWARDED_FOR'];
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
}

/**
 * Get audit logs with filters
 * 
 * @param array $filters Filters for the query
 * @param int $page Page number for pagination
 * @param int $perPage Items per page
 * @return array Audit logs
 */
function getAuditLogs($filters = [], $page = 1, $perPage = 20) {
    $where = [];
    $params = [];
    
    if (!empty($filters['user_id'])) {
        $where[] = "a.userid = ?";
        $params[] = $filters['user_id'];
    }
    
    if (!empty($filters['table'])) {
        $where[] = "a.affectedtable = ?";
        $params[] = $filters['table'];
    }
    
    if (!empty($filters['date_from'])) {
        $where[] = "DATE(a.created_at) >= ?";
        $params[] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $where[] = "DATE(a.created_at) <= ?";
        $params[] = $filters['date_to'];
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    $offset = ($page - 1) * $perPage;
    $params[] = $perPage;
    $params[] = $offset;
    
    $sql = "SELECT a.*, u.name as user_name, u.email as user_email 
            FROM AUDITLOG a
            LEFT JOIN USERS u ON a.userid = u.user_id
            $whereClause
            ORDER BY a.created_at DESC
            LIMIT ? OFFSET ?";
    
    return Database::fetchAll($sql, $params);
}

/**
 * Get total count of audit logs
 */
function getAuditLogsCount($filters = []) {
    $where = [];
    $params = [];
    
    if (!empty($filters['user_id'])) {
        $where[] = "userid = ?";
        $params[] = $filters['user_id'];
    }
    
    if (!empty($filters['table'])) {
        $where[] = "affectedtable = ?";
        $params[] = $filters['table'];
    }
    
    if (!empty($filters['date_from'])) {
        $where[] = "DATE(created_at) >= ?";
        $params[] = $filters['date_from'];
    }
    
    if (!empty($filters['date_to'])) {
        $where[] = "DATE(created_at) <= ?";
        $params[] = $filters['date_to'];
    }
    
    $whereClause = !empty($where) ? "WHERE " . implode(" AND ", $where) : "";
    
    $sql = "SELECT COUNT(*) as total FROM AUDITLOG $whereClause";
    $result = Database::fetchOne($sql, $params);
    
    return $result['total'] ?? 0;
}
