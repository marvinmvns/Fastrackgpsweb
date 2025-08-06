<?php

declare(strict_types=1);

namespace FastrackGps\Core\Database;

use FastrackGps\Core\Exception\DatabaseException;
use PDO;
use PDOException;
use PDOStatement;

final class QueryBuilder
{
    private string $query = '';
    private array $bindings = [];
    private string $table = '';

    public function __construct(
        private readonly DatabaseConnectionInterface $connection
    ) {
    }

    public function table(string $table): self
    {
        $this->table = $table;
        return $this;
    }

    public function select(array $columns = ['*']): self
    {
        $columnsStr = implode(', ', $columns);
        $this->query = "SELECT {$columnsStr} FROM {$this->table}";
        return $this;
    }

    public function where(string $column, string $operator, mixed $value): self
    {
        $placeholder = $this->createPlaceholder($column);
        
        if (empty($this->query)) {
            throw new DatabaseException('Query not initialized. Call select() first.');
        }

        if (str_contains($this->query, 'WHERE')) {
            $this->query .= " AND {$column} {$operator} :{$placeholder}";
        } else {
            $this->query .= " WHERE {$column} {$operator} :{$placeholder}";
        }

        $this->bindings[$placeholder] = $value;
        return $this;
    }

    public function orWhere(string $column, string $operator, mixed $value): self
    {
        $placeholder = $this->createPlaceholder($column);
        
        if (!str_contains($this->query, 'WHERE')) {
            return $this->where($column, $operator, $value);
        }

        $this->query .= " OR {$column} {$operator} :{$placeholder}";
        $this->bindings[$placeholder] = $value;
        return $this;
    }

    public function orderBy(string $column, string $direction = 'ASC'): self
    {
        $direction = strtoupper($direction);
        if (!in_array($direction, ['ASC', 'DESC'], true)) {
            throw new DatabaseException('Order direction must be ASC or DESC');
        }

        if (str_contains($this->query, 'ORDER BY')) {
            $this->query .= ", {$column} {$direction}";
        } else {
            $this->query .= " ORDER BY {$column} {$direction}";
        }

        return $this;
    }

    public function limit(int $limit): self
    {
        $this->query .= " LIMIT {$limit}";
        return $this;
    }

    public function insert(array $data): bool
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);

        $columnsStr = implode(', ', $columns);
        $placeholdersStr = implode(', ', $placeholders);

        $this->query = "INSERT INTO {$this->table} ({$columnsStr}) VALUES ({$placeholdersStr})";
        $this->bindings = $data;

        return $this->execute();
    }

    public function update(array $data): bool
    {
        $setParts = [];
        foreach ($data as $column => $value) {
            $setParts[] = "{$column} = :{$column}";
            $this->bindings[$column] = $value;
        }

        $setClause = implode(', ', $setParts);
        $this->query = "UPDATE {$this->table} SET {$setClause}";

        return $this->execute();
    }

    public function delete(): bool
    {
        $this->query = "DELETE FROM {$this->table}";
        return $this->execute();
    }

    public function get(): array
    {
        try {
            $stmt = $this->prepare();
            $stmt->execute($this->bindings);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            throw DatabaseException::queryFailed($this->query, $e->getMessage());
        }
    }

    public function first(): ?array
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function count(): int
    {
        $originalQuery = $this->query;
        $this->query = str_replace('SELECT *', 'SELECT COUNT(*) as count', $this->query);
        
        try {
            $result = $this->first();
            $this->query = $originalQuery;
            return (int) ($result['count'] ?? 0);
        } catch (DatabaseException $e) {
            $this->query = $originalQuery;
            throw $e;
        }
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    private function execute(): bool
    {
        try {
            $stmt = $this->prepare();
            return $stmt->execute($this->bindings);
        } catch (PDOException $e) {
            throw DatabaseException::queryFailed($this->query, $e->getMessage());
        }
    }

    private function prepare(): PDOStatement
    {
        return $this->connection->getConnection()->prepare($this->query);
    }

    private function createPlaceholder(string $column): string
    {
        static $counter = 0;
        return $column . '_' . (++$counter);
    }

    public function getQuery(): string
    {
        return $this->query;
    }

    public function getBindings(): array
    {
        return $this->bindings;
    }
}