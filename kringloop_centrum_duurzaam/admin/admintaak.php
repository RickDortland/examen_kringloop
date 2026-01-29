<?php
session_start();
require_once '../config/database.php';

// Error reporting voor debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// beveiliging: alleen directie
if (!isset($_SESSION['gebruiker']) || $_SESSION['gebruiker']['rollen'] !== 'directie') {
    header("Location: ../../login.php");
    exit;
}

/* =========================
   CREATE USER
========================= */
if (isset($_POST['create_user'])) {
    $gebruikersnaam = trim($_POST['gebruikersnaam']);
    $wachtwoord = password_hash($_POST['wachtwoord'], PASSWORD_DEFAULT);
    $rol = $_POST['rol'];

    try {
        // Zet id op NULL zodat MySQL een waarde kiest
        $stmt = $pdo->prepare("
            INSERT INTO gebruiker (id, gebruikersnaam, wachtwoord, rollen, is_geverifieerd)
            VALUES (NULL, ?, ?, ?, 1)
        ");
        $stmt->execute([$gebruikersnaam, $wachtwoord, $rol]);
        
        // Refresh de pagina om form resubmission te voorkomen
        header("Location: ".$_SERVER['PHP_SELF']);
        exit;
        
    } catch (PDOException $e) {
        $error = "Fout bij aanmaken gebruiker: " . $e->getMessage();
    }
}

/* =========================
   DELETE USER
========================= */
if (isset($_GET['delete'])) {
    $stmt = $pdo->prepare("DELETE FROM gebruiker WHERE id = ?");
    $stmt->execute([$_GET['delete']]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

/* =========================
   RESET PASSWORD
========================= */
if (isset($_POST['reset_password'])) {
    $nieuwWachtwoord = password_hash("Welkom123", PASSWORD_DEFAULT);

    $stmt = $pdo->prepare("
        UPDATE gebruiker 
        SET wachtwoord = ?
        WHERE id = ?
    ");
    $stmt->execute([$nieuwWachtwoord, $_POST['user_id']]);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit;
}

/* =========================
   READ USERS
========================= */
try {
    $stmt = $pdo->query("SELECT id, gebruikersnaam, rollen FROM gebruiker ORDER BY id");
    $gebruikers = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $error = "Fout bij ophalen gebruikers: " . $e->getMessage();
    $gebruikers = [];
}
?>
<!DOCTYPE html>
<html lang="nl">
<head>
<meta charset="UTF-8">
<title>Admin Beheer</title>
<link rel="stylesheet" href="../assets/css/style.css">
<style>
    body {
        font-family: Arial, sans-serif;
        background: #f2f2f2;
        padding: 30px;
    }
    h1 {
        color: #2c3e50;
        margin-bottom: 20px;
    }
    .error {
        background: #ffebee;
        color: #c62828;
        padding: 12px;
        border-radius: 4px;
        margin-bottom: 20px;
        border-left: 4px solid #c62828;
    }
    form, table {
        background: white;
        padding: 20px;
        margin-bottom: 25px;
        border-radius: 6px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }
    input, select, button {
        padding: 10px;
        margin: 5px 0;
        width: 100%;
        box-sizing: border-box;
        border: 1px solid #ddd;
        border-radius: 4px;
    }
    button {
        background: #3498db;
        color: white;
        border: none;
        cursor: pointer;
        font-size: 16px;
        margin-top: 10px;
    }
    button:hover {
        background: #2980b9;
    }
    table {
        width: 100%;
        border-collapse: collapse;
    }
    th, td {
        padding: 12px;
        border-bottom: 1px solid #ddd;
        text-align: left;
    }
    th {
        background: #34495e;
        color: white;
    }
    tr:hover {
        background: #f9f9f9;
    }
    .delete {
        color: #e74c3c;
        text-decoration: none;
        padding: 6px 12px;
        border: 1px solid #e74c3c;
        border-radius: 4px;
        margin-left: 10px;
    }
    .delete:hover {
        background: #e74c3c;
        color: white;
    }
    .reset {
        background: #f39c12;
        border: none;
        color: white;
        padding: 6px 12px;
        border-radius: 4px;
        cursor: pointer;
    }
    .reset:hover {
        background: #d68910;
    }
    .acties {
        white-space: nowrap;
    }
</style>
</head>
<body>

<h1>Admin Beheerpanel</h1>

<?php if (isset($error)): ?>
    <div class="error"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

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
<h2>Gebruikersoverzicht</h2>
<?php if (count($gebruikers) > 0): ?>
<table>
<tr>
    <th>ID</th>
    <th>Gebruikersnaam</th>
    <th>Rol</th>
    <th>Acties</th>
</tr>

<?php foreach ($gebruikers as $g): ?>
<tr>
    <td><?= htmlspecialchars($g['id']) ?></td>
    <td><?= htmlspecialchars($g['gebruikersnaam']) ?></td>
    <td><?= htmlspecialchars($g['rollen']) ?></td>
    <td class="acties">
        <form method="post" style="display:inline;">
            <input type="hidden" name="user_id" value="<?= $g['id'] ?>">
            <button class="reset" name="reset_password">
                Reset wachtwoord
            </button>
        </form>

        <a class="delete"
           href="?delete=<?= $g['id'] ?>"
           onclick="return confirm('Weet je zeker dat je <?= htmlspecialchars($g['gebruikersnaam']) ?> wilt verwijderen?')">
           Verwijderen
        </a>
    </td>
</tr>
<?php endforeach; ?>
</table>
<?php else: ?>
    <p>Er zijn nog geen gebruikers aangemaakt.</p>
<?php endif; ?>

</body>
</html>