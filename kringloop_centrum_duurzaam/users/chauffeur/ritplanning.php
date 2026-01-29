// Auteur : Rayan en Rick
// Inhoud : Logica voor het ritplanning voor chauffeurs
// Laatst bijgewerkt : 29 - 01 - 2026
<?php
session_start();
require_once '../../config/database.php';

// checkt of je ingelogd ben als chauffeur
if (!isset($_SESSION['gebruiker']) || $_SESSION['gebruiker']['rollen'] !== 'chauffeur') {
    header("Location: ../../../login.php");
    exit;
}

$chauffeur_id = $_SESSION['gebruiker']['id'];
$message = '';
$error = '';

//delete
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $stmt = $pdo->prepare("DELETE FROM planning WHERE id = ?");
    $stmt->execute([$_POST['id']]);
    $message = "Rit verwijderd";
}

// ADD / EDIT
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && in_array($_POST['action'], ['add', 'edit'])) {

    $artikel_id = $_POST['artikel_id'];
    $klant_id = $_POST['klant_id'];
    $kenteken = $_POST['kenteken'];
    $ophalen_of_bezorgen = $_POST['ophalen_of_bezorgen'];
    $afspraak_op = $_POST['afspraak_op'];

    // Check of artikel bestaat (foreign key fix)
    $checkArtikel = $pdo->prepare("SELECT id FROM artikel WHERE id = ?");
    $checkArtikel->execute([$artikel_id]);

    if ($checkArtikel->rowCount() === 0) {
        $error = "Geselecteerd artikel bestaat niet.";
    } else {
        if ($_POST['action'] === 'add') {
            $stmt = $pdo->prepare("
                INSERT INTO planning 
                (artikel_id, klant_id, kenteken, ophalen_of_bezorgen, afspraak_op)
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$artikel_id, $klant_id, $kenteken, $ophalen_of_bezorgen, $afspraak_op]);
            $message = "Rit toegevoegd";
        } else {
            $stmt = $pdo->prepare("
                UPDATE planning 
                SET artikel_id = ?, klant_id = ?, kenteken = ?, ophalen_of_bezorgen = ?, afspraak_op = ?
                WHERE id = ?
            ");
            $stmt->execute([
                $artikel_id,
                $klant_id,
                $kenteken,
                $ophalen_of_bezorgen,
                $afspraak_op,
                $_POST['id']
            ]);
            $message = "Rit bijgewerkt";
        }
    }
}

$ritten = $pdo->query("SELECT * FROM planning ORDER BY afspraak_op DESC")->fetchAll(PDO::FETCH_ASSOC);
$artikelen = $pdo->query("SELECT id, naam FROM artikel ORDER BY naam")->fetchAll(PDO::FETCH_ASSOC);

// Edit mode
$edit_rit = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM planning WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $edit_rit = $stmt->fetch(PDO::FETCH_ASSOC);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Ritplanning</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .container { max-width: 1000px; margin: auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 10px; }
        th { background: #4CAF50; color: white; }
        form { background: #f9f9f9; padding: 15px; border-radius: 5px; }
        input, select { padding: 8px; width: 100%; margin: 5px 0; }
        button { padding: 10px 20px; border: none; cursor: pointer; }
        .success { background: #d4edda; color: #155724; padding: 10px; margin-bottom: 10px; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 10px; }
        .delete-btn { background: #f44336; color: white; }
    </style>
</head>
<body>

<div class="container">
    <h1>Ritplanning</h1>

    <?php if ($message): ?><div class="success"><?= htmlspecialchars($message) ?></div><?php endif; ?>
    <?php if ($error): ?><div class="error"><?= htmlspecialchars($error) ?></div><?php endif; ?>

    <h2><?= $edit_rit ? 'Rit bewerken' : 'Nieuwe rit toevoegen' ?></h2>

    <form method="POST">
        <input type="hidden" name="action" value="<?= $edit_rit ? 'edit' : 'add' ?>">
        <?php if ($edit_rit): ?>
            <input type="hidden" name="id" value="<?= $edit_rit['id'] ?>">
        <?php endif; ?>

        <label>Artikel</label>
        <select name="artikel_id" required>
            <option value="">-- Kies artikel --</option>
            <?php foreach ($artikelen as $artikel): ?>
                <option value="<?= $artikel['id'] ?>"
                    <?= ($edit_rit['artikel_id'] ?? '') == $artikel['id'] ? 'selected' : '' ?>>
                    <?= $artikel['id'] ?> - <?= htmlspecialchars($artikel['naam']) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <input type="number" name="klant_id" placeholder="Klant ID" required value="<?= $edit_rit['klant_id'] ?? '' ?>">
        <input type="text" name="kenteken" placeholder="Kenteken" required value="<?= $edit_rit['kenteken'] ?? '' ?>">

        <select name="ophalen_of_bezorgen" required>
            <option value="">-- Selecteer --</option>
            <option value="ophalen" <?= ($edit_rit['ophalen_of_bezorgen'] ?? '') === 'ophalen' ? 'selected' : '' ?>>Ophalen</option>
            <option value="bezorgen" <?= ($edit_rit['ophalen_of_bezorgen'] ?? '') === 'bezorgen' ? 'selected' : '' ?>>Bezorgen</option>
        </select>

        <input type="datetime-local" name="afspraak_op" required value="<?= $edit_rit['afspraak_op'] ?? '' ?>">

        <button type="submit"><?= $edit_rit ? 'Bijwerken' : 'Toevoegen' ?></button>
        <?php if ($edit_rit): ?>
            <a href="ritplanning.php"><button type="button">Annuleren</button></a>
        <?php endif; ?>
    </form>

    <h2>Geplande ritten</h2>
    <table>
        <tr>
            <th>ID</th>
            <th>Artikel</th>
            <th>Klant</th>
            <th>Kenteken</th>
            <th>Type</th>
            <th>Afspraak</th>
            <th>Acties</th>
        </tr>

        <?php foreach ($ritten as $rit): ?>
            <tr>
                <td><?= $rit['id'] ?></td>
                <td><?= $rit['artikel_id'] ?></td>
                <td><?= $rit['klant_id'] ?></td>
                <td><?= htmlspecialchars($rit['kenteken']) ?></td>
                <td><?= htmlspecialchars($rit['ophalen_of_bezorgen']) ?></td>
                <td><?= $rit['afspraak_op'] ?></td>
                <td>
                    <a href="?edit=<?= $rit['id'] ?>"><button>Bewerken</button></a>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="action" value="delete">
                        <input type="hidden" name="id" value="<?= $rit['id'] ?>">
                        <button class="delete-btn" onclick="return confirm('Zeker weten?')">Verwijderen</button>
                    </form>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>

</div>
</body>
</html>