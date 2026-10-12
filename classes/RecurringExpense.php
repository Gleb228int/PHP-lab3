<?php
require_once __DIR__ . '/Transaction.php';

class RecurringExpense extends Transaction {
    protected string $frequency;

    public function __construct(float $amount, string $category, string $date, string $frequency) {
        parent::__construct($amount, $category, $date);
        $this->frequency = $frequency;
    }

    public function getFrequency(): string {
        return $this->frequency;
    }

    public function getInfo(): string {
        $parentInfo = parent::getInfo();
        return "{$parentInfo} [Регулярність: {$this->frequency}]";
    }
}
