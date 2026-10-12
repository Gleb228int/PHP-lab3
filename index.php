<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', '1');

require_once 'classes/Transaction.php';
require_once 'classes/RecurringExpense.php';
require_once 'classes/Wallet.php';
require_once 'lib/functions.php';

if (!isset($_SESSION['wallet'])) {
    $_SESSION['wallet'] = new Wallet();
    $_SESSION['wallet']->addTransaction(new Transaction(450, 'Продукти', '2026-09-01'));
    $_SESSION['wallet']->addTransaction(new Transaction(12500, 'Техніка', '2026-09-03'));
    $_SESSION['wallet']->addTransaction(new RecurringExpense(2400, 'Комунальні послуги', '2026-09-12', 'Щомісячно'));
}

$wallet = $_SESSION['wallet'];

$errors = [];
$successMessage = '';
$amountInput = '';
$categoryInput = '';
$dateInput = '';
$typeInput = 'standard';
$frequencyInput = 'Щомісячно';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $amountInput = trim($_POST['amount'] ?? '');
    $categoryInput = trim($_POST['category'] ?? '');
    $dateInput = trim($_POST['date'] ?? '');
    $typeInput = trim($_POST['type'] ?? 'standard');
    $frequencyInput = trim($_POST['frequency'] ?? 'Щомісячно');

    if ($amountInput === '' || !is_numeric($amountInput) || floatval($amountInput) <= 0) {
        $errors['amount'] = 'Сума має бути числом більшим за 0.';
    }

    $allowedCategories = ['Продукти', 'Техніка', 'Транспорт', 'Одяг', 'Розваги', 'Комунальні послуги', 'Інше'];
    if ($categoryInput === '' || !in_array($categoryInput, $allowedCategories)) {
        $errors['category'] = 'Будь ласка, оберіть коректну категорію.';
    }

    if ($dateInput === '') {
        $errors['date'] = 'Дата обов\'язкова.';
    } else {
        $d = DateTime::createFromFormat('Y-m-d', $dateInput);
        if (!$d || $d->format('Y-m-d') !== $dateInput) {
            $errors['date'] = 'Введено некоректну дату.';
        }
    }

    if (empty($errors)) {
        if ($typeInput === 'recurring') {
            $newTx = new RecurringExpense(floatval($amountInput), $categoryInput, $dateInput, $frequencyInput);
        } else {
            $newTx = new Transaction(floatval($amountInput), $categoryInput, $dateInput);
        }


        $wallet->addTransaction($newTx);
        $_SESSION['wallet'] = $wallet; 

        $successMessage = 'Нову транзакцію успішно додано!';
        $amountInput = '';
        $dateInput = '';
    }
}

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
        <h1>Трекер особистих витрат (ООП + Форма)</h1>

        <div class="form-card">
            <h2>Додати нову транзакцію</h2>

            <?php if (!empty($successMessage)): ?>
                <div class="alert success"><?= $successMessage ?></div>
            <?php endif; ?>

            <form id="transactionForm" method="post" action="">
                <div class="form-group">
                    <label for="type">Тип транзакції:</label>
                    <select id="type" name="type">
                        <option value="standard" <?= $typeInput === 'standard' ? 'selected' : '' ?>>Звичайна витрата (Базовий клас)</option>
                        <option value="recurring" <?= $typeInput === 'recurring' ? 'selected' : '' ?>>Регулярна витрата (Похідний клас)</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="amount">Сума (грн):</label>
                    <input type="number" id="amount" name="amount" step="0.01" min="0.01" 
                           value="<?= htmlspecialchars($amountInput) ?>" required>
                    <?php if (isset($errors['amount'])): ?>
                        <span class="error"><?= $errors['amount'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="category">Категорія:</label>
                    <select id="category" name="category" required>
                        <option value="">-- Оберіть категорію --</option>
                        <option value="Продукти" <?= $categoryInput === 'Продукти' ? 'selected' : '' ?>>Продукти</option>
                        <option value="Техніка" <?= $categoryInput === 'Техніка' ? 'selected' : '' ?>>Техніка</option>
                        <option value="Транспорт" <?= $categoryInput === 'Транспорт' ? 'selected' : '' ?>>Транспорт</option>
                        <option value="Одяг" <?= $categoryInput === 'Одяг' ? 'selected' : '' ?>>Одяг</option>
                        <option value="Розваги" <?= $categoryInput === 'Розваги' ? 'selected' : '' ?>>Розваги</option>
                        <option value="Комунальні послуги" <?= $categoryInput === 'Комунальні послуги' ? 'selected' : '' ?>>Комунальні послуги</option>
                        <option value="Інше" <?= $categoryInput === 'Інше' ? 'selected' : '' ?>>Інше</option>
                    </select>
                    <?php if (isset($errors['category'])): ?>
                        <span class="error"><?= $errors['category'] ?></span>
                    <?php endif; ?>
                </div>

                <div class="form-group" id="frequencyGroup">
                    <label for="frequency">Регулярність:</label>
                    <input type="text" id="frequency" name="frequency" value="<?= htmlspecialchars($frequencyInput) ?>" placeholder="Наприклад: Щомісячно">
                </div>

                <div class="form-group">
                    <label for="date">Дата:</label>
                    <input type="date" id="date" name="date" 
                           value="<?= htmlspecialchars($dateInput ?: date('Y-m-d')) ?>" required>
                    <?php if (isset($errors['date'])): ?>
                        <span class="error"><?= $errors['date'] ?></span>
                    <?php endif; ?>
                </div>

                <button type="submit" class="btn">Додати витрату</button>
            </form>
        </div>

        <h2>Список транзакцій (Колекція Wallet)</h2>
        <table>
            <thead>
                <tr>
                    <th>№</th>
                    <th>Опис транзакції (getInfo)</th>
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

    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const categorySelect = document.getElementById('category');
            const typeSelect = document.getElementById('type');
            const frequencyGroup = document.getElementById('frequencyGroup');
            const form = document.getElementById('transactionForm');
            const amountInput = document.getElementById('amount');

            function toggleFrequency() {
                if (typeSelect.value === 'recurring') {
                    frequencyGroup.style.display = 'block';
                } else {
                    frequencyGroup.style.display = 'none';
                }
            }
            toggleFrequency();
            typeSelect.addEventListener('change', toggleFrequency);

            const savedCategory = localStorage.getItem('last_category');
            if (savedCategory && !categorySelect.value) {
                categorySelect.value = savedCategory;
            }

            categorySelect.addEventListener('change', () => {
                if (categorySelect.value) {
                    localStorage.setItem('last_category', categorySelect.value);
                }
            });

            form.addEventListener('submit', (event) => {
                const amountVal = parseFloat(amountInput.value);
                if (isNaN(amountVal) || amountVal <= 0) {
                    alert('Помилка (JS): Сума має бути числом більшим за 0!');
                    event.preventDefault();
                    amountInput.focus();
                }
            });
        });
    </script>
</body>
</html>
