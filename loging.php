<?php
session_start();
require_once 'kringloop_centrum_duurzaam/config/database.php';
include 'kringloop_centrum_duurzaam/includes/header.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $stmt = $pdo->prepare("SELECT * FROM user WHERE gebruikersnaam = ?");
    $stmt->execute([trim($_POST['gebruikersnaam'])]);
    $user = $stmt-;>fetch(PDO::FETCH_ASSOC)

    if ($user && password_verify(trim($_POST['wachtwoord']), $user['wachtwoord'])) {

        $_SESSION['gebruiker'] = $user;

      
        if ($user['gebruiker'] == directie) {
            header("Location: kringloop_centrum_duurzaam/admin/admintaak.php");
        } 
        if ($user['gebruiker'] == magazijnmedewerker){
            header("Location: kringloop_centrum_duurzaam/users/magazijnmedewerker/magazijnmedewerker.php")
        }
        if ($user['gebruiker'] == winkerpersoneel){
            header("Location: kringloop_centrum_duurzaam/users/winkerpersoneel/winkerpersoneel.php")
        }
        if ($user['gebruiker'] == chauffuer){
            header("Location: kringloop_centrum_duurzaam/users/chauffuer/chauffuer.php")
        }
        exit;

    } else {
        $error = "Gebruikersnaam of wachtwoord onjuist";
    }
}

?>

<form method="post">
    <h2>Inloggen</h2>

    <input type="text" name="username" placeholder="Gebruikersnaam" required>
    <input type="password" name="password" placeholder="Wachtwoord" required>

    <button type="submit">Login</button>

    <a href="forgot_password.php">Wachtwoord vergeten?</a>

    <?php if (isset($error)) echo "<p style='color:red'>$error</p>"; ?>
</form>

<?php include 'kringloop_centrum_duurzaam/includes/footer.php'; ?>
