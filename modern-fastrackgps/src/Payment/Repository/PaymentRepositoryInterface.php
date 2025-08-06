<?php

declare(strict_types=1);

namespace FastrackGps\Payment\Repository;

use FastrackGps\Payment\Entity\Payment;
use DateTimeImmutable;

interface PaymentRepositoryInterface
{
    public function findById(int $id): ?Payment;
    
    public function findByClientId(int $clientId): array;
    
    public function findByClientIdAndDateRange(
        int $clientId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array;
    
    public function findPendingByClientId(int $clientId): array;
    
    public function findOverduePayments(): array;
    
    public function save(Payment $payment): void;
    
    public function delete(int $id): void;
    
    public function getTotalByClientId(int $clientId): float;
    
    public function getTotalByClientIdAndYear(int $clientId, int $year): float;
    
    public function getMonthlyTotalsByClientId(int $clientId, int $year): array;
    
    public function countPendingByClientId(int $clientId): int;
    
    public function findAll(int $limit = 100, int $offset = 0): array;
}