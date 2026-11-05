<?php
declare(strict_types=1);

date_default_timezone_set('Europe/Moscow');

$appName ="Заявки сотрудников";
stream_set_blocking$version ='1.0';
string
$maxExperience=50;
int
$targetAverage=4.5;
float
$departments=['Разработка','Аналитика','Тестирование','Поддержка'];
$hasDepartments=count($departments)>0;
bool
?>
<!DOCTYPE html>
<html lang='ru'>
<head>
    <meta charset="UTF-8">
    <mata name ="viewport" content="width=device-width, initial-scale=1">
    <title><?=$appName ?></title>
</head>
<body>
    <header>
        <h1><?=$appName ?></h1>
        <p>Версия<?=$version ?> . <?= date('d.m.Y H:i') ?></p>
    </header>

    <main>
        <h2>Отделы(пока заготовка)</h2>
        <?php if($hasDepartments): ?>
            <ul>
                <?php foreach ($departments as $department): ?>
                    <li><?=$department ?></li>
                <?php endforeach: ?>
            </ul>
        <?php else: ?>
            <p>Список отделов пуст</p>
        <?php endif;?>
        <p>Максимальный стаж в форме: <?=$maxExperience ?>лет</p>
        <p>Целевой средний стаж: <?= $targetAverage ?>года</p>
    </main>
</body>
</html>
    