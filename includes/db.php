<?php
/**
 * Bharat SEO - Database Layer (PDO singleton)
 */
if (!defined('BASE_PATH')) { http_response_code(403); exit('Forbidden'); }

class DB
{
    private static ?PDO $pdo = null;

    public static function conn(): ?PDO
    {
        if (self::$pdo instanceof PDO) {
            return self::$pdo;
        }
        try {
            $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
            self::$pdo = new PDO($dsn, DB_USER, DB_PASS, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
            return self::$pdo;
        } catch (Throwable $e) {
            log_error('DB connection failed: ' . $e->getMessage());
            return null;
        }
    }

    /** Run a prepared statement, return PDOStatement or null on failure. */
    public static function run(string $sql, array $params = []): ?PDOStatement
    {
        $pdo = self::conn();
        if (!$pdo) {
            return null;
        }
        try {
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (Throwable $e) {
            log_error('Query failed: ' . $e->getMessage() . ' | SQL: ' . $sql);
            return null;
        }
    }

    /** Fetch a single row (assoc) or null. */
    public static function row(string $sql, array $params = []): ?array
    {
        $stmt = self::run($sql, $params);
        if (!$stmt) {
            return null;
        }
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** Fetch all rows; always returns an array (possibly empty). */
    public static function all(string $sql, array $params = []): array
    {
        $stmt = self::run($sql, $params);
        if (!$stmt) {
            return [];
        }
        return $stmt->fetchAll();
    }

    /** Fetch a single scalar value or default. */
    public static function value(string $sql, array $params = [], $default = null)
    {
        $stmt = self::run($sql, $params);
        if (!$stmt) {
            return $default;
        }
        $val = $stmt->fetchColumn();
        return $val !== false ? $val : $default;
    }

    /** Insert helper; returns last insert id or null. */
    public static function insert(string $table, array $data): ?int
    {
        $cols = array_keys($data);
        $placeholders = array_map(fn($c) => ':' . $c, $cols);
        $sql = 'INSERT INTO `' . $table . '` (`' . implode('`,`', $cols) . '`) VALUES (' . implode(',', $placeholders) . ')';
        $stmt = self::run($sql, $data);
        if (!$stmt) {
            return null;
        }
        return (int) self::conn()->lastInsertId();
    }

    /** Update helper; returns affected rows or 0. */
    public static function update(string $table, array $data, string $where, array $whereParams = []): int
    {
        $set = [];
        foreach (array_keys($data) as $c) {
            $set[] = "`$c` = :$c";
        }
        $sql = 'UPDATE `' . $table . '` SET ' . implode(', ', $set) . ' WHERE ' . $where;
        $stmt = self::run($sql, array_merge($data, $whereParams));
        return $stmt ? $stmt->rowCount() : 0;
    }

    /** Delete helper. */
    public static function delete(string $table, string $where, array $params = []): int
    {
        $stmt = self::run('DELETE FROM `' . $table . '` WHERE ' . $where, $params);
        return $stmt ? $stmt->rowCount() : 0;
    }

    /** Whether the database is reachable. */
    public static function ok(): bool
    {
        return self::conn() instanceof PDO;
    }
}
