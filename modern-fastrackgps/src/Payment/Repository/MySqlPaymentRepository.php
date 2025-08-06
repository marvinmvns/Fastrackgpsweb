<?php

declare(strict_types=1);

namespace FastrackGps\Payment\Repository;

use FastrackGps\Core\Database\DatabaseConnectionInterface;
use FastrackGps\Core\Database\QueryBuilder;
use FastrackGps\Core\Exception\DatabaseException;
use FastrackGps\Payment\Entity\Payment;
use Psr\Log\LoggerInterface;
use DateTimeImmutable;

final class MySqlPaymentRepository implements PaymentRepositoryInterface
{
    private QueryBuilder $queryBuilder;

    public function __construct(
        private readonly DatabaseConnectionInterface $connection,
        private readonly LoggerInterface $logger
    ) {
        $this->queryBuilder = new QueryBuilder($connection);
    }

    public function findById(int $id): ?Payment
    {
        try {
            $data = $this->queryBuilder
                ->table('pagamento')
                ->select([
                    'id', 'cliente_id', 'valor', 'data_vencimento', 'data_pagamento',
                    'metodo_pagamento', 'status', 'descricao', 'observacoes',
                    'numero_referencia', 'created_at', 'updated_at'
                ])
                ->where('id', '=', $id)
                ->first();

            return $data ? Payment::fromArray($data) : null;
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find payment by ID', ['id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByClientId(int $clientId): array
    {
        try {
            $data = $this->queryBuilder
                ->table('pagamento')
                ->select([
                    'id', 'cliente_id', 'valor', 'data_vencimento', 'data_pagamento',
                    'metodo_pagamento', 'status', 'descricao', 'observacoes',
                    'numero_referencia', 'created_at', 'updated_at'
                ])
                ->where('cliente_id', '=', $clientId)
                ->orderBy('data_vencimento', 'DESC')
                ->get();

            return array_map(fn(array $row) => Payment::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find payments by client ID', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findByClientIdAndDateRange(
        int $clientId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array {
        try {
            $data = $this->queryBuilder
                ->table('pagamento')
                ->select([
                    'id', 'cliente_id', 'valor', 'data_vencimento', 'data_pagamento',
                    'metodo_pagamento', 'status', 'descricao', 'observacoes',
                    'numero_referencia', 'created_at', 'updated_at'
                ])
                ->where('cliente_id', '=', $clientId)
                ->where('data_vencimento', '>=', $startDate->format('Y-m-d'))
                ->where('data_vencimento', '<=', $endDate->format('Y-m-d'))
                ->orderBy('data_vencimento', 'DESC')
                ->get();

            return array_map(fn(array $row) => Payment::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find payments by date range', [
                'client_id' => $clientId,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function findPendingByClientId(int $clientId): array
    {
        try {
            $data = $this->queryBuilder
                ->table('pagamento')
                ->select([
                    'id', 'cliente_id', 'valor', 'data_vencimento', 'data_pagamento',
                    'metodo_pagamento', 'status', 'descricao', 'observacoes',
                    'numero_referencia', 'created_at', 'updated_at'
                ])
                ->where('cliente_id', '=', $clientId)
                ->where('status', '=', 'pending')
                ->orderBy('data_vencimento')
                ->get();

            return array_map(fn(array $row) => Payment::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find pending payments', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findOverduePayments(): array
    {
        try {
            $data = $this->queryBuilder
                ->table('pagamento')
                ->select([
                    'id', 'cliente_id', 'valor', 'data_vencimento', 'data_pagamento',
                    'metodo_pagamento', 'status', 'descricao', 'observacoes',
                    'numero_referencia', 'created_at', 'updated_at'
                ])
                ->where('status', '=', 'pending')
                ->where('data_vencimento', '<', date('Y-m-d'))
                ->orderBy('data_vencimento')
                ->get();

            return array_map(fn(array $row) => Payment::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find overdue payments', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function save(Payment $payment): void
    {
        try {
            if ($payment->getId() > 0) {
                $this->update($payment);
            } else {
                $this->insert($payment);
            }
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to save payment', ['payment_id' => $payment->getId(), 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function delete(int $id): void
    {
        try {
            $this->queryBuilder
                ->table('pagamento')
                ->where('id', '=', $id)
                ->delete();

            $this->logger->info('Payment deleted successfully', ['payment_id' => $id]);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to delete payment', ['payment_id' => $id, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getTotalByClientId(int $clientId): float
    {
        try {
            $result = $this->queryBuilder
                ->table('pagamento')
                ->select(['SUM(valor) as total'])
                ->where('cliente_id', '=', $clientId)
                ->where('status', '=', 'paid')
                ->first();

            return (float) ($result['total'] ?? 0);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to get total payments by client ID', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function getTotalByClientIdAndYear(int $clientId, int $year): float
    {
        try {
            $result = $this->queryBuilder
                ->table('pagamento')
                ->select(['SUM(valor) as total'])
                ->where('cliente_id', '=', $clientId)
                ->where('status', '=', 'paid')
                ->where('YEAR(data_pagamento)', '=', $year)
                ->first();

            return (float) ($result['total'] ?? 0);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to get total payments by year', [
                'client_id' => $clientId,
                'year' => $year,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function getMonthlyTotalsByClientId(int $clientId, int $year): array
    {
        try {
            $data = $this->queryBuilder
                ->table('pagamento')
                ->select(['MONTH(data_pagamento) as month', 'SUM(valor) as total'])
                ->where('cliente_id', '=', $clientId)
                ->where('status', '=', 'paid')
                ->where('YEAR(data_pagamento)', '=', $year)
                ->groupBy('MONTH(data_pagamento)')
                ->orderBy('MONTH(data_pagamento)')
                ->get();

            $monthlyTotals = array_fill(1, 12, 0.0);
            foreach ($data as $row) {
                $monthlyTotals[(int) $row['month']] = (float) $row['total'];
            }

            return $monthlyTotals;
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to get monthly totals', [
                'client_id' => $clientId,
                'year' => $year,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function countPendingByClientId(int $clientId): int
    {
        try {
            return $this->queryBuilder
                ->table('pagamento')
                ->where('cliente_id', '=', $clientId)
                ->where('status', '=', 'pending')
                ->count();
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to count pending payments', ['client_id' => $clientId, 'error' => $e->getMessage()]);
            throw $e;
        }
    }

    public function findAll(int $limit = 100, int $offset = 0): array
    {
        try {
            $data = $this->queryBuilder
                ->table('pagamento')
                ->select([
                    'id', 'cliente_id', 'valor', 'data_vencimento', 'data_pagamento',
                    'metodo_pagamento', 'status', 'descricao', 'observacoes',
                    'numero_referencia', 'created_at', 'updated_at'
                ])
                ->orderBy('data_vencimento', 'DESC')
                ->limit($limit)
                ->offset($offset)
                ->get();

            return array_map(fn(array $row) => Payment::fromArray($row), $data);
        } catch (DatabaseException $e) {
            $this->logger->error('Failed to find all payments', ['error' => $e->getMessage()]);
            throw $e;
        }
    }

    private function insert(Payment $payment): void
    {
        $data = $payment->toArray();
        unset($data['id']);
        
        $this->queryBuilder->table('pagamento')->insert($data);
        $this->logger->info('New payment created', ['client_id' => $payment->getClientId()]);
    }

    private function update(Payment $payment): void
    {
        $data = $payment->toArray();
        $id = $data['id'];
        unset($data['id']);
        
        $this->queryBuilder
            ->table('pagamento')
            ->where('id', '=', $id)
            ->update($data);

        $this->logger->info('Payment updated', ['payment_id' => $payment->getId()]);
    }
}