<?php

declare(strict_types=1);

namespace FastrackGps\Payment\Controller;

use FastrackGps\Core\Exception\ValidationException;
use FastrackGps\Core\Http\JsonResponse;
use FastrackGps\Core\Http\Request;
use FastrackGps\Core\Http\Response;
use FastrackGps\Security\CsrfTokenManager;
use FastrackGps\Payment\Service\PaymentService;
use FastrackGps\Payment\ValueObject\PaymentMethod;
use FastrackGps\Auth\Service\AuthenticationService;
use Psr\Log\LoggerInterface;
use DateTimeImmutable;

final class PaymentController
{
    public function __construct(
        private readonly PaymentService $paymentService,
        private readonly AuthenticationService $authService,
        private readonly CsrfTokenManager $csrfManager,
        private readonly LoggerInterface $logger
    ) {
    }

    public function index(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return Response::redirect('/login');
        }

        try {
            $payments = $this->paymentService->getPaymentsByClient($user->getId());

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'payments' => array_map(fn($payment) => [
                        'id' => $payment->getId(),
                        'amount' => $payment->getAmount(),
                        'due_date' => $payment->getDueDate()->format('Y-m-d'),
                        'payment_date' => $payment->getPaymentDate()?->format('Y-m-d'),
                        'method' => $payment->getMethod()->getDisplayName(),
                        'status' => $payment->getStatus()->getDisplayName(),
                        'description' => $payment->getDescription(),
                        'reference_number' => $payment->getReferenceNumber()
                    ], $payments)
                ]);
            }

            return Response::view('payment/index', [
                'payments' => $payments,
                'user' => $user
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to list payments', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to load payments');
            }
            
            return Response::view('payment/index', [
                'error' => 'Erro ao carregar pagamentos',
                'payments' => []
            ]);
        }
    }

    public function show(Request $request, int $id): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $payments = $this->paymentService->getPaymentsByClient($user->getId());
            $payment = null;
            
            foreach ($payments as $p) {
                if ($p->getId() === $id) {
                    $payment = $p;
                    break;
                }
            }

            if ($payment === null) {
                if ($request->expectsJson()) {
                    return JsonResponse::error('Payment not found', [], 404);
                }
                return Response::view('error/404');
            }

            $paymentData = [
                'id' => $payment->getId(),
                'client_id' => $payment->getClientId(),
                'amount' => $payment->getAmount(),
                'due_date' => $payment->getDueDate()->format('Y-m-d'),
                'payment_date' => $payment->getPaymentDate()?->format('Y-m-d'),
                'method' => $payment->getMethod()->getDisplayName(),
                'status' => $payment->getStatus()->getDisplayName(),
                'description' => $payment->getDescription(),
                'reference_number' => $payment->getReferenceNumber(),
                'notes' => $payment->getNotes(),
                'created_at' => $payment->getCreatedAt()->format('Y-m-d H:i:s'),
                'updated_at' => $payment->getUpdatedAt()->format('Y-m-d H:i:s')
            ];

            if ($request->expectsJson()) {
                return JsonResponse::success(['payment' => $paymentData]);
            }

            return Response::view('payment/show', [
                'payment' => $payment,
                'payment_data' => $paymentData
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to show payment', ['id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to load payment');
            }
            return Response::view('error/500');
        }
    }

    public function create(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null || !$user->isAdmin()) {
            return JsonResponse::forbidden();
        }

        if ($request->isPost()) {
            return $this->store($request);
        }

        $csrfToken = $this->csrfManager->generateToken('create_payment');

        return Response::view('payment/create', [
            'csrf_token' => $csrfToken,
            'payment_methods' => PaymentMethod::getAllOptions()
        ]);
    }

    public function store(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null || !$user->isAdmin()) {
            return JsonResponse::forbidden();
        }

        try {
            $this->csrfManager->requireValidToken($request->input('_token'), 'create_payment');

            $paymentData = [
                'client_id' => (int) $request->input('client_id'),
                'amount' => (float) $request->input('amount'),
                'due_date' => $request->input('due_date'),
                'method' => $request->input('method'),
                'description' => $request->input('description'),
                'reference_number' => $request->input('reference_number')
            ];

            $payment = $this->paymentService->createPayment($paymentData);

            $this->logger->info('Payment created successfully', [
                'payment_id' => $payment->getId(),
                'client_id' => $payment->getClientId(),
                'amount' => $payment->getAmount(),
                'created_by' => $user->getId()
            ]);

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'payment' => [
                        'id' => $payment->getId(),
                        'amount' => $payment->getAmount(),
                        'due_date' => $payment->getDueDate()->format('Y-m-d'),
                        'method' => $payment->getMethod()->getDisplayName()
                    ]
                ], 'Payment created successfully', 201);
            }

            return Response::redirect('/payments/' . $payment->getId());

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return JsonResponse::error('Validation failed', $e->getErrors(), 422);
            }

            return Response::view('payment/create', [
                'errors' => $e->getErrors(),
                'old' => $request->only(['client_id', 'amount', 'due_date', 'method', 'description']),
                'csrf_token' => $this->csrfManager->generateToken('create_payment'),
                'payment_methods' => PaymentMethod::getAllOptions()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to create payment', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to create payment');
            }

            return Response::view('payment/create', [
                'error' => 'Erro ao criar pagamento',
                'old' => $request->only(['client_id', 'amount', 'due_date', 'method', 'description']),
                'csrf_token' => $this->csrfManager->generateToken('create_payment'),
                'payment_methods' => PaymentMethod::getAllOptions()
            ]);
        }
    }

    public function process(Request $request, int $id): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null || !$user->isAdmin()) {
            return JsonResponse::forbidden();
        }

        try {
            $this->csrfManager->requireValidToken($request->input('_token'), 'process_payment');

            $processData = [
                'payment_date' => $request->input('payment_date'),
                'notes' => $request->input('notes')
            ];

            $payment = $this->paymentService->processPayment($id, $processData);

            $this->logger->info('Payment processed successfully', [
                'payment_id' => $id,
                'processed_by' => $user->getId()
            ]);

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'payment' => [
                        'id' => $payment->getId(),
                        'status' => $payment->getStatus()->getDisplayName(),
                        'payment_date' => $payment->getPaymentDate()?->format('Y-m-d')
                    ]
                ], 'Payment processed successfully');
            }

            return Response::redirect('/payments/' . $id);

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return JsonResponse::error('Validation failed', $e->getErrors(), 422);
            }
            return Response::redirect('/payments/' . $id . '?error=validation');
        } catch (\Exception $e) {
            $this->logger->error('Failed to process payment', ['id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to process payment');
            }
            return Response::redirect('/payments/' . $id . '?error=processing');
        }
    }

    public function statistics(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $year = (int) ($request->input('year') ?? date('Y'));
            $statistics = $this->paymentService->getPaymentStatistics($user->getId(), $year);

            if ($request->expectsJson()) {
                return JsonResponse::success(['statistics' => $statistics]);
            }

            return Response::view('payment/statistics', [
                'statistics' => $statistics,
                'year' => $year
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to get payment statistics', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to load statistics');
            }
            return Response::view('error/500');
        }
    }

    public function report(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $startDate = new DateTimeImmutable($request->input('start_date', '-30 days'));
            $endDate = new DateTimeImmutable($request->input('end_date', 'now'));

            $report = $this->paymentService->generatePaymentReport($user->getId(), $startDate, $endDate);

            if ($request->expectsJson()) {
                return JsonResponse::success(['report' => $report]);
            }

            return Response::view('payment/report', [
                'report' => $report,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d')
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to generate payment report', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to generate report');
            }
            return Response::view('error/500');
        }
    }

    public function pending(Request $request): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null) {
            return JsonResponse::unauthorized();
        }

        try {
            $pendingPayments = $this->paymentService->getPendingPaymentsByClient($user->getId());

            if ($request->expectsJson()) {
                return JsonResponse::success([
                    'payments' => array_map(fn($payment) => [
                        'id' => $payment->getId(),
                        'amount' => $payment->getAmount(),
                        'due_date' => $payment->getDueDate()->format('Y-m-d'),
                        'description' => $payment->getDescription(),
                        'days_overdue' => $payment->getDueDate() < new DateTimeImmutable() 
                            ? (new DateTimeImmutable())->diff($payment->getDueDate())->days
                            : 0
                    ], $pendingPayments)
                ]);
            }

            return Response::view('payment/pending', [
                'payments' => $pendingPayments
            ]);

        } catch (\Exception $e) {
            $this->logger->error('Failed to get pending payments', ['error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to load pending payments');
            }
            return Response::view('error/500');
        }
    }

    public function delete(Request $request, int $id): Response
    {
        $user = $this->authService->getCurrentUser();
        if ($user === null || !$user->isAdmin()) {
            return JsonResponse::forbidden();
        }

        try {
            $this->csrfManager->requireValidToken($request->input('_token'), 'delete_payment');

            $this->paymentService->deletePayment($id);

            $this->logger->info('Payment deleted successfully', [
                'payment_id' => $id,
                'deleted_by' => $user->getId()
            ]);

            if ($request->expectsJson()) {
                return JsonResponse::success([], 'Payment deleted successfully');
            }

            return Response::redirect('/payments');

        } catch (ValidationException $e) {
            if ($request->expectsJson()) {
                return JsonResponse::error('Payment not found', [], 404);
            }
            return Response::view('error/404');
        } catch (\Exception $e) {
            $this->logger->error('Failed to delete payment', ['id' => $id, 'error' => $e->getMessage()]);
            
            if ($request->expectsJson()) {
                return JsonResponse::error('Failed to delete payment');
            }
            return Response::view('error/500');
        }
    }
}