<?php

namespace App\ValueObjects;

use InvalidArgumentException;

/**
 * Value Object para representar un monto monetario.
 * Asegura que los valores monetarios sean inmutables y válidos.
 */
class Money
{
    private float $amount;

    public function __construct(float $amount)
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('El monto no puede ser negativo.');
        }

        $this->amount = round($amount, 2);
    }

    public function getAmount(): float
    {
        return $this->amount;
    }

    public function add(Money $other): self
    {
        return new self($this->amount + $other->amount);
    }

    public function subtract(Money $other): self
    {
        $result = $this->amount - $other->amount;
        if ($result < 0) {
            throw new InvalidArgumentException('El resultado no puede ser negativo.');
        }
        return new self($result);
    }

    public function multiply(float $factor): self
    {
        if ($factor < 0) {
            throw new InvalidArgumentException('El factor no puede ser negativo.');
        }
        return new self($this->amount * $factor);
    }

    public function equals(Money $other): bool
    {
        return $this->amount === $other->amount;
    }

    public function toString(): string
    {
        return number_format($this->amount, 2, '.', '');
    }
}
