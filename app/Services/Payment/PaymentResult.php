<?php

namespace App\Services\Payment;

class PaymentResult
{
    public const STATUS_SUCCESS = 'success';
    public const STATUS_PENDING = 'pending';
    public const STATUS_FAILED = 'failed';
    public const STATUS_CANCELLED = 'cancelled';

    public function __construct(
        protected string $status,
        protected ?string $transactionId = null,
        protected ?string $message = null,
        protected array $rawPayload = []
    ) {}

    public static function success(?string $transactionId = null, ?string $message = 'Payment completed successfully.', array $rawPayload = []): self
    {
        return new self(self::STATUS_SUCCESS, $transactionId, $message, $rawPayload);
    }

    public static function pending(?string $transactionId = null, ?string $message = 'Payment is processing.', array $rawPayload = []): self
    {
        return new self(self::STATUS_PENDING, $transactionId, $message, $rawPayload);
    }

    public static function failed(?string $message = 'Payment failed or was declined.', array $rawPayload = []): self
    {
        return new self(self::STATUS_FAILED, null, $message, $rawPayload);
    }

    public static function cancelled(?string $message = 'Payment was cancelled by the user.', array $rawPayload = []): self
    {
        return new self(self::STATUS_CANCELLED, null, $message, $rawPayload);
    }

    public function isSuccessful(): bool
    {
        return $this->status === self::STATUS_SUCCESS;
    }

    public function isPending(): bool
    {
        return $this->status === self::STATUS_PENDING;
    }

    public function isFailed(): bool
    {
        return $this->status === self::STATUS_FAILED;
    }

    public function isCancelled(): bool
    {
        return $this->status === self::STATUS_CANCELLED;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTransactionId(): ?string
    {
        return $this->transactionId;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function getRawPayload(): array
    {
        return $this->rawPayload;
    }
}
