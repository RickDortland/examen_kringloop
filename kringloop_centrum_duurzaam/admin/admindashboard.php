<?php
session_start();
require_once '../config/database.php';

// beveiliging: alleen directie
if (!isset($_SESSION['gebruiker']) || $_SESSION['gebruiker']['rollen'] !== 'directie') {
    header("Location: ../../login.php");
    exit;
}


//CREATE USER
if (isset($_POST['create_user'])) {
    $gebruikersnaam = trim($_POST['gebruikersnaam']);
    $wachtwoord = password_hash($_POST['wachtwoord'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];

    $stmt = $pdo->prepare("
        INSERT INTO gebruiker (gebruikersnaam, wachtwoord, rollen, is_geverifieerd)
        VALUES (?, ?, ?, 1)
    ");
    $stmt->execute([$gebruikersnaam, $wachtwoord, $rol]);
}

//DELETE USER
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM gebruiker WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
}

// WACHTWOORD RESETTEN
if (isset($_POST['reset_password'])) {
    $nieuwWachtwoord = password_hash("Welkom123", PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        UPDATE gebruiker 
        SET wachtwoord = ?
        WHERE id = ?
    ");
    $stmt->execute([$nieuwWachtwoord, $_POST['user_id']]);
}

$stmt = $pdo->query("SELECT id, gebruikersnaam, rollen FROM gebruiker");
// Haalt alle resultaten op als associatieve arrays
$gebruikers = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <link rel="stylesheet" href="../assets/css/admindash.css">
<meta charset="UTF-8">
<title>Admin Beheer</title>

</head>
<body>

<h1>Admin Beheerpanel</h1>

<!-- USER AANMAKEN -->
<form method="post">
    <h2>Nieuwe gebruiker aanmaken</h2>

    <input type="text" name="gebruikersnaam" placeholder="Gebruikersnaam" required>
    <input type="password" name="wachtwoord" placeholder="Wachtwoord" required>

    <select name="rol" required>
        <option value="">-- Kies rol --</option>
        <option value="directie">Directie (admin)</option>
        <option value="magazijnmedewerker">Magazijnmedewerker</option>
        <option value="winkelpersoneel">Winkelpersoneel</option>
        <option value="chauffeur">Chauffeur</option>
    </select>

    <button type="submit" name="create_user">Gebruiker aanmaken</button>
</form>

<!-- GEBRUIKERS OVERZICHT -->
<table>
<!-- maakt een rij in een tabel -->
<tr>
    <th>ID</th>
    <th>Gebruikersnaam</th>
    <th>Rol</th>
    <th>Acties</th>
</tr>

<?php foreach ($gebruikers as $g): ?>
<tr>
    <td><?= $g['id'] ?></td>
    <td><?= htmlspecialchars($g['gebruikersnaam']) ?></td>
    <td><?= $g['rollen'] ?></td>
    <td>
        <form method="post" style="display:inline;">
            <input type="hidden" name="user_id" value="<?= $g['id'] ?>">
            <button class="reset" name="reset_password">
                Reset wachtwoord
            </button>
        </form>

        |
        <a class="delete"
           href="?delete=<?= $g['id'] ?>"
           onclick="return confirm('Weet je zeker dat je deze gebruiker wilt verwijderen?')">
           Verwijderen
        </a>
    </td>
</tr>
<?php endforeach; ?>
</table>

</body>
</html>
