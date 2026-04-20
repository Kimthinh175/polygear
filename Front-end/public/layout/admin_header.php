<?php
// admin_header.php
?>
<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $data['title'] ?? 'Admin Portal' ?></title>
    <base href="<?= BASE_URL ?>">
    <link href="https:// fonts.googleapis.com/css2?family=inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https:// cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="shortcut icon" href="img/layout/logo-mark.avif" type="image/x-icon">

    <!-- Base styles for Admin -->
    <link rel="stylesheet" href="css/admin/admin_style.css">

    <?php if (!empty($data['css'])): ?>
        <?php foreach ($data['css'] as $css): ?>
            <link rel="stylesheet" href="css/admin/<?= $css ?>">
        <?php endforeach; ?>
    <?php endif; ?>
</head>

<body>
    <div class="app-container">
