<?php
/**
 * DDZ 斗地主 - 数据库操作封装
 * 基于 PDO 预处理语句，消除 SQL 注入漏洞
 * 
 * 修复: P0#1 (SQL注入), P1#8 (双重查询), P0#5 (事务支持)
 */

class DB
{
    /** @var PDO|null */
    private static $pdo = null;

    /** @var array 配置 */
    private static $config = [];

    /**
     * 初始化数据库连接
     * @param array $config 数据库配置 ['host', 'port', 'user', 'pass', 'name', 'charset']
     */
    public static function init(array $config): void
    {
        self::$config = $config;
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['port'] ?? '3306',
            $config['name'],
            $config['charset'] ?? 'utf8mb4'
        );

        try {
            self::$pdo = new PDO($dsn, $config['user'], $config['pass'], [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,  // 真正的预处理，不模拟
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$config['charset']}",
            ]);
        } catch (PDOException $e) {
            // 生产环境返回通用错误，避免泄露数据库信息
            http_response_code(500);
            echo json_encode(['code' => 500, 'message' => 'Database connection failed']);
            exit;
        }
    }

    /**
     * 获取 PDO 实例
     */
    public static function pdo(): PDO
    {
        if (self::$pdo === null) {
            throw new RuntimeException('DB not initialized. Call DB::init() first.');
        }
        return self::$pdo;
    }

    /**
     * 查询并返回多行数据
     * @param string $sql   SQL 语句，使用 ? 占位符
     * @param array  $params 参数数组
     * @return array 结果数组（关联数组）
     */
    public static function select(string $sql, array $params = []): array
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * 查询并返回单行数据
     * @param string $sql   SQL 语句
     * @param array  $params 参数数组
     * @return array|null 关联数组 或 null
     */
    public static function selectOne(string $sql, array $params = []): ?array
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * 执行 INSERT/UPDATE/DELETE 语句
     * @param string $sql   SQL 语句
     * @param array  $params 参数数组
     * @return int 受影响的行数
     */
    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::pdo()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    /**
     * 获取最后插入的ID
     */
    public static function lastInsertId(): string
    {
        return self::pdo()->lastInsertId();
    }

    /**
     * 在事务中执行回调
     * @param callable $callback function(): void
     * @throws Throwable
     */
    public static function transaction(callable $callback): void
    {
        $pdo = self::pdo();
        if ($pdo->inTransaction()) {
            // 已经在一个事务中，直接执行
            $callback();
            return;
        }

        try {
            $pdo->beginTransaction();
            $callback();
            $pdo->commit();
        } catch (Throwable $e) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }
            throw $e;
        }
    }
}
