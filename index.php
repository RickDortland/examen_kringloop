<?php
include 'kringloop_centrum_duurzaam/includes/header.html';
?>

<html>
<head>
    <link rel="stylesheet" href="kringloop_centrum_duurzaam/assets/css/style.css">
</head>
<body>


    <main class="hero">
        <?php if ($isLoggedIn): ?>
            <h1>Welkom terug, <?php echo htmlspecialchars($username); ?>!</h1>
            <p>Je bent ingelogd. Ga verder met je taken of beheer je account.</p>
        <?php else: ?>
            <h1>Welkom bij het KCD</h1>
            <p>Beheer je account.</p>
            <div style="display:flex; gap:12px; justify-content:center; margin-top:12px;">
                <a href="login.php" class="btn btn-secondary">Ga naar login</a>
            </div>
        <?php endif; ?>
    </main>
</body>
</html>