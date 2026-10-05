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

            $usersTableStatement = $this->connection->query(
                "SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'users' LIMIT 1"
            );
            $usersTable = $usersTableStatement->fetchColumn();
            $usersTableStatement->closeCursor();

            if (!$usersTable) {
                $schemaPath = BASE_PATH . '/database/schema.sql';
                $schema = is_file($schemaPath) ? file_get_contents($schemaPath) : false;

                if ($schema === false) {
                    throw new RuntimeException('Database schema file could not be read.');
                }

                $this->connection->exec($schema);
            }

            $requiredTables = [
                'email_verifications' => "CREATE TABLE IF NOT EXISTS `email_verifications` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT, `user_id` INTEGER NOT NULL, `token` VARCHAR(255) NOT NULL, `expires_at` TIMESTAMP NOT NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE );",
                'remember_tokens' => "CREATE TABLE IF NOT EXISTS `remember_tokens` ( `id` INTEGER PRIMARY KEY AUTOINCREMENT, `user_id` INTEGER NOT NULL, `token` VARCHAR(255) NOT NULL, `expires_at` TIMESTAMP NOT NULL, `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE );",
            ];

            foreach ($requiredTables as $table => $sql) {
                $stmt = $this->connection->prepare(
                    "SELECT name FROM sqlite_master WHERE type = 'table' AND name = :table LIMIT 1"
                );
                $stmt->execute(['table' => $table]);
                $tableExists = $stmt->fetchColumn();
                $stmt->closeCursor();

                if (!$tableExists) {
                    $this->connection->exec($sql);
                }
            }

            $columnChecks = [
                'users' => [
                    'is_test_account' => "ALTER TABLE users ADD COLUMN is_test_account INTEGER NOT NULL DEFAULT 0 CHECK (is_test_account IN (0, 1))",
                ],
                'entrepreneurs' => [
                    'plan' => "ALTER TABLE entrepreneurs ADD COLUMN plan TEXT NOT NULL DEFAULT 'free'",
                    'payment_status' => "ALTER TABLE entrepreneurs ADD COLUMN payment_status TEXT NOT NULL DEFAULT 'trial'",
                    'payment_mode' => "ALTER TABLE entrepreneurs ADD COLUMN payment_mode TEXT NOT NULL DEFAULT 'manual_test'",
                ],
                'suppliers' => [
                    'plan' => "ALTER TABLE suppliers ADD COLUMN plan TEXT NOT NULL DEFAULT 'account'",
                    'payment_status' => "ALTER TABLE suppliers ADD COLUMN payment_status TEXT NOT NULL DEFAULT 'trial'",
                    'payment_mode' => "ALTER TABLE suppliers ADD COLUMN payment_mode TEXT NOT NULL DEFAULT 'manual_test'",
                ],
            ];

            foreach ($columnChecks as $table => $columns) {
                $tableInfo = $this->connection->query("PRAGMA table_info($table)")->fetchAll();
                $existingColumns = [];
                foreach ($tableInfo as $column) {
                    $existingColumns[] = $column['name'];
                }

                foreach ($columns as $columnName => $alterSql) {
                    if (!in_array($columnName, $existingColumns, true)) {
                        $this->connection->exec($alterSql);
                        if ($table === 'users' && $columnName === 'is_test_account') {
                            $this->connection->exec("UPDATE users SET is_test_account = 1 WHERE email GLOB 'nexar.demo.20260928.*@example.com' OR email GLOB 'nexar.buyer.20260928.*@example.com'");
                            $this->connection->exec('CREATE INDEX IF NOT EXISTS `idx_users_test_account` ON `users` (`is_test_account`)');
                            $this->connection->exec("UPDATE users SET account_type = 'admin' WHERE role = 'admin'");
                        }
                    }
                }
            }

            $this->migrateLegacySupplierPlans();

            $this->connected = true;
        } catch (PDOException $e) {
            if (is_debug()) {
                throw new Exception('Database connection failed: ' . $e->getMessage());
            }
            throw new Exception('Database connection failed. Please check your configuration.');
        }
    }

    private function migrateLegacySupplierPlans(): void {
        $tableStatement = $this->connection->query(
            "SELECT sql FROM sqlite_master WHERE type = 'table' AND name = 'suppliers'"
        );
        $tableSql = $tableStatement->fetchColumn();
        $tableStatement->closeCursor();

        if (!is_string($tableSql)
            || !str_contains($tableSql, "'basic'")
            || !str_contains($tableSql, "'professional'")
            || !str_contains($tableSql, "'premium'")) {
            return;
        }

        $columns = [
            'id', 'user_id', 'cnpj', 'legal_name', 'trade_name', 'phone', 'city', 'state',
            'business_segment', 'logo', 'cover_image', 'description', 'category', 'main_products',
            'service_region', 'website', 'whatsapp', 'plan', 'payment_status', 'payment_mode',
            'metadata', 'created_at', 'updated_at',
        ];
        $columnsStatement = $this->connection->query('PRAGMA table_info(suppliers)');
        $existingColumns = array_column($columnsStatement->fetchAll(), 'name');
        $columnsStatement->closeCursor();

        if (array_diff($columns, $existingColumns) || array_diff($existingColumns, $columns)) {
            throw new RuntimeException('Cannot migrate suppliers table with an unexpected schema.');
        }

        $columnList = implode(', ', array_map(static fn(string $column): string => '`' . $column . '`', $columns));

        $this->connection->beginTransaction();
        try {
            $this->connection->exec("CREATE TABLE `suppliers_new` (
                `id` INTEGER PRIMARY KEY AUTOINCREMENT,
                `user_id` INTEGER NOT NULL,
                `cnpj` VARCHAR(18) NULL DEFAULT NULL,
                `legal_name` VARCHAR(255) NULL DEFAULT NULL,
                `trade_name` VARCHAR(255) NULL DEFAULT NULL,
                `phone` VARCHAR(20) NULL DEFAULT NULL,
                `city` VARCHAR(100) NULL DEFAULT NULL,
                `state` VARCHAR(2) NULL DEFAULT NULL,
                `business_segment` VARCHAR(150) NULL DEFAULT NULL,
                `logo` BLOB NULL,
                `cover_image` BLOB NULL,
                `description` TEXT NULL,
                `category` VARCHAR(150) NULL DEFAULT NULL,
                `main_products` TEXT NULL,
                `service_region` VARCHAR(150) NULL DEFAULT NULL,
                `website` VARCHAR(255) NULL DEFAULT NULL,
                `whatsapp` VARCHAR(50) NULL DEFAULT NULL,
                `plan` TEXT NOT NULL DEFAULT 'account' CHECK (`plan` IN ('account','boost','promoted')),
                `payment_status` TEXT NOT NULL DEFAULT 'trial' CHECK (`payment_status` IN ('trial','pending','paid','canceled')),
                `payment_mode` TEXT NOT NULL DEFAULT 'manual_test' CHECK (`payment_mode` IN ('manual_test','gateway','trial')),
                `metadata` TEXT NULL,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
            )");
            $this->connection->exec("INSERT INTO `suppliers_new` ($columnList)
                SELECT `id`, `user_id`, `cnpj`, `legal_name`, `trade_name`, `phone`, `city`, `state`,
                    `business_segment`, `logo`, `cover_image`, `description`, `category`, `main_products`,
                    `service_region`, `website`, `whatsapp`,
                    CASE `plan` WHEN 'basic' THEN 'account' WHEN 'professional' THEN 'boost'
                        WHEN 'premium' THEN 'promoted' ELSE `plan` END,
                    `payment_status`, `payment_mode`, `metadata`, `created_at`, `updated_at`
                FROM `suppliers`");
            $this->connection->exec('DROP TABLE `suppliers`');
            $this->connection->exec('ALTER TABLE `suppliers_new` RENAME TO `suppliers`');
            $this->connection->exec('CREATE INDEX IF NOT EXISTS `idx_suppliers_user_id` ON `suppliers` (`user_id`)');
            $this->connection->exec('CREATE INDEX IF NOT EXISTS `idx_suppliers_cnpj` ON `suppliers` (`cnpj`)');
            $this->connection->exec('CREATE INDEX IF NOT EXISTS `idx_suppliers_state` ON `suppliers` (`state`)');
            $this->connection->exec('CREATE INDEX IF NOT EXISTS `idx_suppliers_category` ON `suppliers` (`category`)');
            $this->connection->commit();
        } catch (Throwable $e) {
            if ($this->connection->inTransaction()) {
                $this->connection->rollBack();
            }
            throw $e;
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