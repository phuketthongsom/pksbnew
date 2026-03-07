<?php
// admin_page_title and admin_active_nav must be set before including this
require_login();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title><?= esc($admin_page_title ?? 'Admin') ?> — PKSB Admin</title>
  <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
<div class="admin-wrap">
  <aside class="admin-sidebar">
    <div class="sidebar-brand">
      <img src="../assets/images/logo.png" alt="PKSB" style="height:32px">
      <span>PKSB Admin</span>
    </div>
    <nav>
      <a href="dashboard.php"  class="<?= ($admin_active_nav??'')==='dashboard'?'active':'' ?>"><span class="icon">📊</span> Dashboard</a>
      <a href="timetable.php"  class="<?= ($admin_active_nav??'')==='timetable'?'active':'' ?>"><span class="icon">🕐</span> Timetable</a>
      <a href="tt_images.php"  class="<?= ($admin_active_nav??'')==='tt_images'?'active':'' ?>"><span class="icon">🖼</span> Timetable Images</a>
      <a href="routes.php"     class="<?= ($admin_active_nav??'')==='routes'?'active':'' ?>"><span class="icon">🗺</span> Routes</a>
      <a href="attractions.php" class="<?= ($admin_active_nav??'')==='attractions'?'active':'' ?>"><span class="icon">🗺️</span> Attractions</a>
      <a href="passes.php"     class="<?= ($admin_active_nav??'')==='passes'?'active':'' ?>"><span class="icon">🎫</span> Passes</a>
      <a href="content.php"    class="<?= ($admin_active_nav??'')==='content'?'active':'' ?>"><span class="icon">✏️</span> Content</a>
      <a href="users.php"      class="<?= ($admin_active_nav??'')==='users'?'active':'' ?>"><span class="icon">👤</span> Users</a>
      <a href="../index.php"   target="_blank"><span class="icon">🌐</span> View Site</a>
      <a href="logout.php" style="margin-top:auto;color:#e74c3c!important"><span class="icon">🚪</span> Logout</a>
    </nav>
  </aside>
  <div class="admin-content">
    <div class="admin-topbar">
      <h1><?= esc($admin_page_title ?? '') ?></h1>
      <span style="font-size:.85rem;color:#888">Logged in as <strong><?= esc($_SESSION['admin_name'] ?? '') ?></strong></span>
    </div>
    <div class="admin-main">
