<?php

declare(strict_types=1);

namespace FastrackGps\Payment\Service;

use FastrackGps\Core\Exception\ValidationException;
use FastrackGps\Core\Exception\BusinessException;
use FastrackGps\Payment\Entity\Payment;
use FastrackGps\Payment\Repository\PaymentRepositoryInterface;
use FastrackGps\Payment\ValueObject\PaymentMethod;
use FastrackGps\Payment\ValueObject\PaymentStatus;
use FastrackGps\Auth\Repository\UserRepositoryInterface;
use Psr\Log\LoggerInterface;
use DateTimeImmutable;

final class PaymentService
{
    public function __construct(
        private readonly PaymentRepositoryInterface $paymentRepository,
        private readonly UserRepositoryInterface $userRepository,
        private readonly LoggerInterface $logger
    ) {
    }

    public function createPayment(array $data): Payment
    {
        $this->validatePaymentData($data);

        $client = $this->userRepository->findById($data['client_id']);
        if ($client === null) {
            throw new ValidationException(['client_id' => 'Cliente não encontrado']);
        }

        $payment = Payment::create(
            clientId: $data['client_id'],
            amount: (float) $data['amount'],
            dueDate: new DateTimeImmutable($data['due_date']),
            method: PaymentMethod::from($data['method']),
            description: $data['description'] ?? '',
            referenceNumber: $data['reference_number'] ?? null
        );

        $this->paymentRepository->save($payment);

        $this->logger->info('Payment created successfully', [
            'payment_id' => $payment->getId(),
            'client_id' => $payment->getClientId(),
            'amount' => $payment->getAmount()
        ]);

        return $payment;
    }

    public function processPayment(int $paymentId, array $data): Payment
    {
        $payment = $this->paymentRepository->findById($paymentId);
        if ($payment === null) {
            throw new ValidationException(['payment' => 'Pagamento não encontrado']);
        }

        if ($payment->getStatus() !== PaymentStatus::PENDING) {
            throw new BusinessException('Pagamento já foi processado');
        }

        $paymentDate = isset($data['payment_date']) 
            ? new DateTimeImmutable($data['payment_date'])
            : new DateTimeImmutable();

        $payment = $payment->markAsPaid($paymentDate, $data['notes'] ?? null);

        $this->paymentRepository->save($payment);

        $this->logger->info('Payment processed successfully', [
            'payment_id' => $paymentId,
            'client_id' => $payment->getClientId(),
            'amount' => $payment->getAmount(),
            'payment_date' => $paymentDate->format('Y-m-d H:i:s')
        ]);

        return $payment;
    }

    public function cancelPayment(int $paymentId, string $reason = null): Payment
    {
        $payment = $this->paymentRepository->findById($paymentId);
        if ($payment === null) {
            throw new ValidationException(['payment' => 'Pagamento não encontrado']);
        }

        if ($payment->getStatus() === PaymentStatus::PAID) {
            throw new BusinessException('Não é possível cancelar um pagamento já processado');
        }

        $payment = $payment->cancel($reason);
        $this->paymentRepository->save($payment);

        $this->logger->info('Payment cancelled', [
            'payment_id' => $paymentId,
            'reason' => $reason
        ]);

        return $payment;
    }

    public function updatePayment(int $paymentId, array $data): Payment
    {
        $payment = $this->paymentRepository->findById($paymentId);
        if ($payment === null) {
            throw new ValidationException(['payment' => 'Pagamento não encontrado']);
        }

        if ($payment->getStatus() === PaymentStatus::PAID) {
            throw new BusinessException('Não é possível alterar um pagamento já processado');
        }

        $this->validateUpdateData($data);

        $updateData = [];
        if (isset($data['amount'])) {
            $updateData['amount'] = (float) $data['amount'];
        }
        if (isset($data['due_date'])) {
            $updateData['due_date'] = new DateTimeImmutable($data['due_date']);
        }
        if (isset($data['method'])) {
            $updateData['method'] = PaymentMethod::from($data['method']);
        }
        if (isset($data['description'])) {
            $updateData['description'] = $data['description'];
        }
        if (isset($data['reference_number'])) {
            $updateData['reference_number'] = $data['reference_number'];
        }

        $payment = $payment->update($updateData);
        $this->paymentRepository->save($payment);

        $this->logger->info('Payment updated', ['payment_id' => $paymentId]);

        return $payment;
    }

    public function getPaymentsByClient(int $clientId): array
    {
        return $this->paymentRepository->findByClientId($clientId);
    }

    public function getPendingPaymentsByClient(int $clientId): array
    {
        return $this->paymentRepository->findPendingByClientId($clientId);
    }

    public function getPaymentsByDateRange(
        int $clientId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array {
        return $this->paymentRepository->findByClientIdAndDateRange($clientId, $startDate, $endDate);
    }

    public function getOverduePayments(): array
    {
        return $this->paymentRepository->findOverduePayments();
    }

    public function getPaymentStatistics(int $clientId, int $year = null): array
    {
        $year = $year ?? (int) date('Y');

        $totalPaid = $this->paymentRepository->getTotalByClientIdAndYear($clientId, $year);
        $pendingCount = $this->paymentRepository->countPendingByClientId($clientId);
        $monthlyTotals = $this->paymentRepository->getMonthlyTotalsByClientId($clientId, $year);

        return [
            'total_paid' => $totalPaid,
            'pending_count' => $pendingCount,
            'monthly_totals' => $monthlyTotals,
            'year' => $year
        ];
    }

    public function deletePayment(int $paymentId): void
    {
        $payment = $this->paymentRepository->findById($paymentId);
        if ($payment === null) {
            throw new ValidationException(['payment' => 'Pagamento não encontrado']);
        }

        if ($payment->getStatus() === PaymentStatus::PAID) {
            throw new BusinessException('Não é possível excluir um pagamento já processado');
        }

        $this->paymentRepository->delete($paymentId);

        $this->logger->info('Payment deleted', ['payment_id' => $paymentId]);
    }

    public function generatePaymentReport(
        int $clientId,
        DateTimeImmutable $startDate,
        DateTimeImmutable $endDate
    ): array {
        $payments = $this->getPaymentsByDateRange($clientId, $startDate, $endDate);

        $report = [
            'period' => [
                'start' => $startDate->format('Y-m-d'),
                'end' => $endDate->format('Y-m-d')
            ],
            'summary' => [
                'total_payments' => count($payments),
                'total_amount' => 0.0,
                'paid_amount' => 0.0,
                'pending_amount' => 0.0,
                'overdue_amount' => 0.0
            ],
            'by_method' => [],
            'by_status' => [],
            'payments' => []
        ];

        $today = new DateTimeImmutable();

        foreach ($payments as $payment) {
            $amount = $payment->getAmount();
            $method = $payment->getMethod()->value;
            $status = $payment->getStatus()->value;

            $report['summary']['total_amount'] += $amount;

            if ($payment->getStatus() === PaymentStatus::PAID) {
                $report['summary']['paid_amount'] += $amount;
            } elseif ($payment->getStatus() === PaymentStatus::PENDING) {
                $report['summary']['pending_amount'] += $amount;
                if ($payment->getDueDate() < $today) {
                    $report['summary']['overdue_amount'] += $amount;
                }
            }

            if (!isset($report['by_method'][$method])) {
                $report['by_method'][$method] = ['count' => 0, 'amount' => 0.0];
            }
            $report['by_method'][$method]['count']++;
            $report['by_method'][$method]['amount'] += $amount;

            if (!isset($report['by_status'][$status])) {
                $report['by_status'][$status] = ['count' => 0, 'amount' => 0.0];
            }
            $report['by_status'][$status]['count']++;
            $report['by_status'][$status]['amount'] += $amount;

            $report['payments'][] = [
                'id' => $payment->getId(),
                'amount' => $amount,
                'due_date' => $payment->getDueDate()->format('Y-m-d'),
                'payment_date' => $payment->getPaymentDate()?->format('Y-m-d'),
                'method' => $payment->getMethod()->getDisplayName(),
                'status' => $payment->getStatus()->getDisplayName(),
                'description' => $payment->getDescription(),
                'reference_number' => $payment->getReferenceNumber()
            ];
        }

        return $report;
    }

    private function validatePaymentData(array $data): void
    {
        $errors = [];

        if (empty($data['client_id']) || !is_numeric($data['client_id'])) {
            $errors['client_id'] = 'ID do cliente é obrigatório';
        }

        if (empty($data['amount']) || !is_numeric($data['amount']) || (float) $data['amount'] <= 0) {
            $errors['amount'] = 'Valor deve ser maior que zero';
        }

        if (empty($data['due_date'])) {
            $errors['due_date'] = 'Data de vencimento é obrigatória';
        } else {
            try {
                new DateTimeImmutable($data['due_date']);
            } catch (\Exception $e) {
                $errors['due_date'] = 'Data de vencimento inválida';
            }
        }

        if (empty($data['method'])) {
            $errors['method'] = 'Método de pagamento é obrigatório';
        } else {
            try {
                PaymentMethod::from($data['method']);
            } catch (\ValueError $e) {
                $errors['method'] = 'Método de pagamento inválido';
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }

    private function validateUpdateData(array $data): void
    {
        $errors = [];

        if (isset($data['amount']) && (!is_numeric($data['amount']) || (float) $data['amount'] <= 0)) {
            $errors['amount'] = 'Valor deve ser maior que zero';
        }

        if (isset($data['due_date'])) {
            try {
                new DateTimeImmutable($data['due_date']);
            } catch (\Exception $e) {
                $errors['due_date'] = 'Data de vencimento inválida';
            }
        }

        if (isset($data['method'])) {
            try {
                PaymentMethod::from($data['method']);
            } catch (\ValueError $e) {
                $errors['method'] = 'Método de pagamento inválido';
            }
        }

        if (!empty($errors)) {
            throw new ValidationException($errors);
        }
    }
}