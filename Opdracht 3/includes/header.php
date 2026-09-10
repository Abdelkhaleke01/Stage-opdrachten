<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'AutoReservering') ?></title>
    <link rel="stylesheet" href="<?= e($assetBase ?? '') ?>css/style.css">
</head>
<body>
<header class="site-header">
    <div class="container">
        <a class="logo" href="<?= e($assetBase ?? '') ?>index.php">🚗 AutoReservering</a>
        <nav>
            <a href="<?= e($assetBase ?? '') ?>index.php">Auto's</a>
            <a href="<?= e($assetBase ?? '') ?>admin/login.php">Beheerder</a>
        </nav>
    </div>
</header>
<main>
    <div class="container">
