<?php
session_start();
require_once 'kringloop_centrum_duurzaam/config/database.php';

$message = '';
$show_form = true;

if ($_POST) {
    $stmt = $pdo->prepare("SELECT * FROM gebruiker WHERE gebruikersnaam = ?");
    $stmt->execute([trim($_POST['gebruikersnaam'])]);
    
    if ($stmt->rowCount() > 0) {
        $nieuw_wachtwoord = bin2hex(random_bytes(4));// bin2hex zorgt ervoor dat het leesbare string wordt van 0tot9 en a tot f
        $hashed = password_hash($nieuw_wachtwoord, PASSWORD_DEFAULT);
        
        $pdo->prepare("UPDATE gebruiker SET wachtwoord = ? WHERE gebruikersnaam = ?")
            ->execute([$hashed, trim($_POST['gebruikersnaam'])]);
        
        $message = "Nieuw wachtwoord: <strong>$nieuw_wachtwoord</strong><br>Log in en wijzig dit direct!";
        $show_form = false;
    } else {
        $message = "Gebruikersnaam niet gevonden";
    }
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <title>Wachtwoord Reset</title>

</head>
<body>
    <div class="box">
        <h2>Wachtwoord Vergeten</h2>
        
        <?php if($message): ?>
            <div class="msg"><?= $message ?></div>
        <?php endif; ?>
        
        <?php if($show_form): ?>
        <form method="POST">
            <input type="text" name="gebruikersnaam" placeholder="Gebruikersnaam" required>
            <button type="submit">Nieuw Wachtwoord</button>
        </form>
        <?php endif; ?>
        
<<<<<<< HEAD
        <a href="index.php">← Terug naar inloggen</a>
=======
        <a href="login.php">← Terug naar inloggen</a>
>>>>>>> 8f588b63159eb26a1d57cdb4b21c2a6d3cbe6d85
    </div>
</body>
</html>