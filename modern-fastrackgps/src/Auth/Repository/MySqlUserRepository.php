<?php

declare(strict_types=1);

namespace FastrackGps\Auth\Repository;

use FastrackGps\Auth\Entity\User;
use FastrackGps\Core\Database\DatabaseConnectionInterface;
use FastrackGps\Core\Database\QueryBuilder;
use FastrackGps\Core\Exception\DatabaseException;
use Psr\Log\LoggerInterface;

final class MySqlUserRepository implements UserRepositoryInterface
{
    private QueryBuilder $queryBuilder;

    public function __construct(
        private readonly DatabaseConnectionInterface $connection,
        private readonly LoggerInterface $logger
    ) {
        $this->queryBuilder = new QueryBuilder($connection);
    }

    public function findById(int $id): ?User
    {
        try {
            $userData = $this->queryBuilder
                ->table('cliente')
                ->select([
                    'id', 'nome', 'email', 'apelido', 'senha', 'cpf',
                    'telefone1', 'telefone2', 'endereco', 'master', 'ativo',
                    'data_inativacao', 'observacao'
                ])
                ->where('id', '=', $id)
                ->first();

            return $userData ? User::fromArray($userData) : null;
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find user by ID', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByUsername(string $username): ?User
    {
        try {
            $userData = $this->queryBuilder
                ->table('cliente')
                ->select([
                    'id', 'nome', 'email', 'apelido', 'senha', 'cpf',
                    'telefone1', 'telefone2', 'endereco', 'master', 'ativo',
                    'data_inativacao', 'observacao'
                ])
                ->where('apelido', '=', $username)
                ->first();

            return $userData ? User::fromArray($userData) : null;
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find user by username', ['username' => $username, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByEmail(string $email): ?User
    {
        try {
            $userData = $this->queryBuilder
                ->table('cliente')
                ->select([
                    'id', 'nome', 'email', 'apelido', 'senha', 'cpf',
                    'telefone1', 'telefone2', 'endereco', 'master', 'ativo',
                    'data_inativacao', 'observacao'
                ])
                ->where('email', '=', $email)
                ->first();

            return $userData ? User::fromArray($userData) : null;
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find user by email', ['email' => $email, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByCredentials(string $usernameOrEmail): ?User
    {
        try {
            $userData = $this->queryBuilder
                ->table('cliente')
                ->select([
                    'id', 'nome', 'email', 'apelido', 'senha', 'cpf',
                    'telefone1', 'telefone2', 'endereco', 'master', 'ativo',
                    'data_inativacao', 'observacao'
                ])
                ->where('email', '=', $usernameOrEmail)
                ->orWhere('apelido', '=', $usernameOrEmail)
                ->first();

            return $userData ? User::fromArray($userData) : null;
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find user by credentials', ['credential' => $usernameOrEmail, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function save(User $user): void
    {
        try {
            if ($user->getId() > 0) {
                $this->update($user);
            } else {
                $this->insert($user);
            }
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to save user', ['user_id' => $user->getId(), 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        try {
            $success = $this->queryBuilder
                ->table('cliente')
                ->where('id', '=', $id)
                ->where('master', '=', 'N') // Prevent deleting master users
                ->delete();

            if ($success) {
                $this->logger->info('User deleted successfully', ['user_id' => $id]);
            }
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to delete user', ['user_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        try {
            $usersData = $this->queryBuilder
                ->table('cliente')
                ->select([
                    'id', 'nome', 'email', 'apelido', 'senha', 'cpf',
                    'telefone1', 'telefone2', 'endereco', 'master', 'ativo',
                    'data_inativacao', 'observacao'
                ])
                ->where('master', '=', 'N')
                ->orderBy('nome')
                ->limit($limit)
                ->get();

            return array_map(fn(array $userData) => User::fromArray($userData), $usersData);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find all users', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function countActiveUsers(): int
    {
        try {
            return $this->queryBuilder
                ->table('cliente')
                ->where('master', '=', 'N')
                ->where('ativo', '=', 'S')
                ->count();
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to count active users', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function logAccess(User $user, string $ipAddress): void
    {
        try {
            $this->queryBuilder
                ->table('cliente_log')
                ->insert([
                    'id' => $user->getId(),
                    'ip' => $ipAddress,
                    'created_at' => date('Y-m-d H:i:s')
                ]);

            $this->logger->info('User access logged', ['user_id' => $user->getId(), 'ip' => $ipAddress]);
        } catch (DatabaseException $e) {
            $this->logger->warning('Failed to log user access', ['user_id' => $user->getId(), 'error' => $e->getMessage()]);
            // Don't throw - logging failure shouldn't break authentication
        }
    }

    private function insert(User $user): void
    {
        $data = $user->toArray();
        unset($data['id']); // Remove ID for insert
        
        $this->queryBuilder->table('cliente')->insert($data);
        $this->logger->info('New user created', ['email' => $user->getEmail()]);
    }

    private function update(User $user): void
    {
        $data = $user->toArray();
        $id = $data['id'];
        unset($data['id']); // Remove ID from update data
        
        $this->queryBuilder
            ->table('cliente')
            ->where('id', '=', $id)
            ->update($data);

        $this->logger->info('User updated', ['user_id' => $user->getId()]);
    }
}