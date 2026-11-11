<?php
declare(strict_types=1);
require_once __DIR__ . '/../src/functions.php';

ini_set('display_errors', '1'); 
error_reporting(E_ALL);
date_default_timezone_set('Europe/Moscow');

$employees = loadEmployees(DATA_DIR . '/employees.csv');
$departments = departmentsOf($employees);
$selectedDepartment = inputValue($_GET, 'department');
$visible = filterEmployees($employees, $selectedDepartment);

$appName = 'Заявки сотрудников';
$total   = count($employees);
$avgExp  = $total > 0 ? round(array_sum(array_column($employees, 'experience')) / $total, 1) : 0.0;
$hasData = $total > 0;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= e($appName) ?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header class="top">
    <div class="brand"><?= e($appName) ?></div>
    <span>Версия <?= e(APP_VERSION) ?> · <?= date('d.m.Y H:i') ?></span>
  </header>

  <main class="wrap">
    <section class="stats" aria-label="Сводка">
      <div class="card"><b>Сотрудников</b><?= $total ?></div>
      <div class="card"><b>Отделов</b><?= count($departments) ?></div>
      <div class="card"><b>Средний стаж, лет</b><?= $avgExp ?></div>
    </section>

    <section id="employees" class="panel">
      <h2>Сотрудники</h2>
      <form method="get" class="filter" action="#employees">
        <label for="department-filter">Отдел</label>
        <select id="department-filter" name="department">
          <option value="">Все</option>
          <?php foreach ($departments as $department): ?>
            <option value="<?= e($department) ?>"<?= $department === $selectedDepartment ? ' selected' : '' ?>>
              <?= e($department) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <button type="submit">Показать</button>
      </form>

      <?php if (!$hasData): ?>
        <p class="notice">Не удалось загрузить data/employees.csv.</p>
      <?php elseif ($visible === []): ?>
        <p class="notice">В отделе «<?= e($selectedDepartment) ?>» записей нет.</p>
      <?php else: ?>
        <table>
          <thead>
            <tr><th>ID</th><th>Имя</th><th>Отдел</th><th>Стаж</th><th>Email</th></tr>
          </thead>
          <tbody>
            <?php foreach ($visible as $employee): ?>
              <tr>
                <td><?= $employee['id'] ?></td>
                <td><?= e($employee['name']) ?></td>
                <td><span class="pill"><?= e($employee['department']) ?></span></td>
                <td><?= $employee['experience'] ?></td>
                <td><?= e($employee['email']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
        <p class="muted">Показано записей: <?= count($visible) ?> из <?= $total ?></p>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>