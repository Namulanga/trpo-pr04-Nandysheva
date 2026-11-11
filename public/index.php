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
$fields = ['name', 'email', 'department', 'experience'];
$old    = array_fill_keys($fields, '');
$errors = [];
$saved  = null;

if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST') {
    foreach ($fields as $field) {
        $old[$field] = inputValue($_POST, $field);
    }
    $errors = validateInput($old, $departments);
    if ($errors === []) {
        $saved = $old;
        $old   = array_fill_keys($fields, '');
    }
}
$caseResults = isset($_GET['cases'])
    ? runCases(loadCases(DATA_DIR . '/form_cases.json'), $departments)
    : [];

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
    <section id="form" class="panel">
      <h2>Новая заявка сотрудника</h2>

      <?php if ($saved !== null): ?>
        <div class="ok" role="status">
          Заявка принята: <?= e($saved['name']) ?>, <?= e($saved['department']) ?>,
          стаж <?= (int) $saved['experience'] ?> лет, <?= e($saved['email']) ?>.
        </div>
      <?php elseif ($errors !== []): ?>
        <div class="errors" role="alert">
          <b>Исправьте ошибки:</b>
          <ul>
            <?php foreach ($errors as $message): ?>
              <li><?= e($message) ?></li>
            <?php endforeach; ?>
          </ul>
        </div>
      <?php endif; ?>

      <form method="post" action="#form" novalidate>
        <label for="name">Имя</label>
        <input id="name" name="name" type="text" value="<?= e($old['name']) ?>">
        <?php if (isset($errors['name'])): ?><span class="err"><?= e($errors['name']) ?></span><?php endif; ?>

        <label for="email">Email</label>
        <input id="email" name="email" type="text" value="<?= e($old['email']) ?>">
        <?php if (isset($errors['email'])): ?><span class="err"><?= e($errors['email']) ?></span><?php endif; ?>

        <label for="department">Отдел</label>
        <select id="department" name="department">
          <option value="">— выберите —</option>
          <?php foreach ($departments as $department): ?>
            <option value="<?= e($department) ?>"<?= $department === $old['department'] ? ' selected' : '' ?>>
              <?= e($department) ?>
            </option>
          <?php endforeach; ?>
        </select>
        <?php if (isset($errors['department'])): ?><span class="err"><?= e($errors['department']) ?></span><?php endif; ?>

        <label for="experience">Стаж, лет</label>
        <input id="experience" name="experience" type="text" inputmode="numeric" value="<?= e($old['experience']) ?>">
        <?php if (isset($errors['experience'])): ?><span class="err"><?= e($errors['experience']) ?></span><?php endif; ?>

        <button type="submit">Отправить</button>
      </form>
    </section>
    <?php if ($caseResults !== []): ?>
      <section id="cases" class="panel">
        <h2>Самопроверка по form_cases.json</h2>
        <table>
          <thead><tr><th>Сценарий</th><th>Ожидается</th><th>Получилось</th><th>Ошибки</th><th>Итог</th></tr></thead>
          <tbody>
            <?php foreach ($caseResults as $result): ?>
              <tr>
                <td><?= e($result['case']) ?></td>
                <td><?= $result['expected'] ? 'валидно' : 'ошибка' ?></td>
                <td><?= $result['actual'] ? 'валидно' : 'ошибка' ?></td>
                <td><?= e(implode('; ', $result['errors'])) ?></td>
                <td><?= $result['ok'] ? 'ОК' : 'РАСХОЖДЕНИЕ' ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </section>
    <?php endif; ?>
  </main>
</body>
</html>