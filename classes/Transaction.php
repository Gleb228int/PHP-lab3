<?php
class Transaction {
    protected float $amount;
    protected string $category;
    protected string $date;

    public function __construct(float $amount, string $category, string $date) {
        $this->amount = $amount;
        $this->category = $category;
        $this->date = $date;
    }

    public function getAmount(): float {
        return $this->amount;
    }

    public function getCategory(): string {
        return $this->category;
    }

    public function getDate(): string {
        return $this->date;
    }

    public function getInfo(): string {
        return "Категорія: {$this->category} — Сума: {$this->amount} грн (Дата: {$this->date})";
    }
}
