<?php
require_once __DIR__ . '/Transaction.php';

class Wallet {
    private array $transactions = [];

    public function addTransaction(Transaction $transaction): void {
        $this->transactions[] = $transaction;
    }

    public function getTransactions(): array {
        return $this->transactions;
    }

    public function totalBalance(): float {
        $total = 0;
        foreach ($this->transactions as $tx) {
            $total += $tx->getAmount();
        }
        return $total;
    }

    public function totalByCategory(string $category): float {
        $total = 0;
        foreach ($this->transactions as $tx) {
            if ($tx->getCategory() === $category) {
                $total += $tx->getAmount();
            }
        }
        return $total;
    }
}