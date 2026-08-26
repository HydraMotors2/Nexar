<?php
/**
 * NEXAR - Database Connection Class
 * PDO-based database abstraction layer
 */

defined('NEXAR_APP') or define('NEXAR_APP', true);

class Database {
    private static ?Database $instance = null;
    private ?PDO $connection = null;
    private ?PDOStatement $statement = null;
    private array $config = [];
    private bool $connected = false;

    /**
     * Private constructor for singleton pattern
     */
    private function __construct() {
        $this->config = [
            'path' => DB_PATH,
        ];
        
        $this->connect();
    }

    /**
     * Get singleton instance
     */
    public static function getInstance(): Database {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Establish database connection
     */
    private function connect(): void {
        try {
            $dsn = 'sqlite:' . $this->config['path'];

            $options = [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->connection = new PDO($dsn, null, null, $options);
            $this->connection->exec('PRAGMA foreign_keys = ON');
            $this->connected = true;
        } catch (PDOException $e) {
            if (is_debug()) {
                throw new Exception('Database connection failed: ' . $e->getMessage());
            }
            throw new Exception('Database connection failed. Please check your configuration.');
        }
    }

    /**
     * Get PDO connection
     */
    public function getConnection(): ?PDO {
        return $this->connection;
    }

    /**
     * Check if connected
     */
    public function isConnected(): bool {
        return $this->connected;
    }

    /**
     * Execute a query
     */
    public function query(string $sql, array $params = []): self {
        try {
            $this->statement = $this->connection->prepare($sql);
            $this->statement->execute($params);
        } catch (PDOException $e) {
            if (is_debug()) {
                throw new Exception('Query failed: ' . $e->getMessage());
            }
            throw new Exception('An error occurred while processing your request.');
        }
        
        return $this;
    }

    /**
     * Fetch single row
     */
    public function fetch(int $fetchMode = PDO::FETCH_ASSOC): ?array {
        $result = $this->statement?->fetch($fetchMode);
        return $result ?: null;
    }

    /**
     * Fetch all rows
     */
    public function fetchAll(int $fetchMode = PDO::FETCH_ASSOC): array {
        return $this->statement?->fetchAll($fetchMode) ?: [];
    }

    /**
     * Fetch single column
     */
    public function fetchColumn(int $column = 0): mixed {
        return $this->statement?->fetchColumn($column);
    }

    /**
     * Get row count
     */
    public function rowCount(): int {
        return $this->statement?->rowCount() ?? 0;
    }

    /**
     * Get last insert ID
     */
    public function lastInsertId(): string {
        return $this->connection->lastInsertId();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction(): bool {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit(): bool {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback(): bool {
        return $this->connection->rollBack();
    }

    /**
     * Insert data
     */
    public function insert(string $table, array $data): string {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = sprintf(
            'INSERT INTO %s (%s) VALUES (%s)',
            $table,
            $columns,
            $placeholders
        );
        
        $this->query($sql, $data);
        return $this->lastInsertId();
    }

    /**
     * Update data
     */
    public function update(string $table, array $data, string $where, array $whereParams = []): int {
        $set = [];
        foreach (array_keys($data) as $column) {
            $set[] = "$column = :$column";
        }
        $setString = implode(', ', $set);
        
        $sql = sprintf(
            'UPDATE %s SET %s WHERE %s',
            $table,
            $setString,
            $where
        );
        
        $params = array_merge($data, $whereParams);
        $this->query($sql, $params);
        
        return $this->rowCount();
    }

    /**
     * Delete data
     */
    public function delete(string $table, string $where, array $params = []): int {
        $sql = sprintf('DELETE FROM %s WHERE %s', $table, $where);
        $this->query($sql, $params);
        return $this->rowCount();
    }

    /**
     * Select data
     */
    public function select(string $table, string $columns = '*', string $where = '', array $params = []): array {
        $sql = sprintf('SELECT %s FROM %s', $columns, $table);
        
        if (!empty($where)) {
            $sql .= ' WHERE ' . $where;
        }
        
        $this->query($sql, $params);
        return $this->fetchAll();
    }

    /**
     * Count records
     */
    public function count(string $table, string $where = '', array $params = []): int {
        $sql = sprintf('SELECT COUNT(*) FROM %s', $table);
        
        if (!empty($where)) {
            $sql .= ' WHERE ' . $where;
        }
        
        $this->query($sql, $params);
        return (int) $this->fetchColumn();
    }

    /**
     * Check if table exists
     */
    public function tableExists(string $table): bool {
        $sql = "SELECT name FROM sqlite_master WHERE type = 'table' AND name = :table LIMIT 1";
        $this->query($sql, ['table' => $table]);
        return (bool) $this->fetchColumn();
    }

    /**
     * Get table columns
     */
    public function getTableColumns(string $table): array {
        $sql = 'PRAGMA table_info(' . $this->quoteIdentifier($table) . ')';
        $this->query($sql);
        return $this->fetchAll();
    }

    /**
     * Escape identifier
     */
    public function quoteIdentifier(string $identifier): string {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    /**
     * Close connection
     */
    public function close(): void {
        $this->connection = null;
        $this->connected = false;
    }

    /**
     * Prevent cloning
     */
    private function __clone() {}

    /**
     * Prevent unserialization
     */
    public function __wakeup() {
        throw new Exception('Cannot unserialize singleton');
    }
}

// Helper function for quick database access
function db(): Database {
    return Database::getInstance();
}