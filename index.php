<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'classes/Transaction.php';
require_once 'classes/RecurringExpense.php';
require_once 'classes/Wallet.php';
require_once 'lib/functions.php';

$wallet = new Wallet();

$wallet->addTransaction(new Transaction(450, 'Продукти', '2026-09-01'));
$wallet->addTransaction(new Transaction(12500, 'Техніка', '2026-09-03'));
$wallet->addTransaction(new RecurringExpense(2400, 'Комунальні послуги', '2026-09-12', 'Щомісячно'));
$wallet->addTransaction(new RecurringExpense(500, 'Розваги', '2026-09-15', 'Щотижня'));

$transactions = $wallet->getTransactions();
$totalAmount = $wallet->totalBalance();
$utilitiesTotal = $wallet->totalByCategory('Комунальні послуги');
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Практична робота №3 — ООП Трекер особистих витрат</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="container">
        <h1>Трекер особистих витрат (ООП)</h1>

        <h2>Список транзакцій (Класи та успадкування)</h2>
        <table>
            <thead>
                <tr>
                    <th>№</th>
                    <th>Опис транзакції</th>
                    <th>Місяць</th>
                    <th>Статус</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($transactions as $index => $tx): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($tx->getInfo()) ?></td>
                        <td><?= monthName($tx->getDate()) ?></td>
                        <td>
                            <?php 
                            if ($tx->getAmount() > 1000) {
                                echo '<span class="badge large">Велика витрата</span>';
                            } else {
                                echo '<span class="badge normal">Звичайна</span>';
                            }
                            ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="summary">
            <p>Витрати на комунальні послуги: <strong><?= formatMoney($utilitiesTotal) ?></strong></p>
            <h3>Загальна сума витрат: <?= formatMoney($totalAmount) ?></h3>
        </div>
    </div>
</body>
</html>