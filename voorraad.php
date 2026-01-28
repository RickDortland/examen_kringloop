<?php
// Databaseverbinding en sessiebeheer
include "kringloop_centrum_duurzaam\config\database.php";
$db = new PDO("mysql:host=localhost;dbname=duurzaam", "root", ""); 
// Ophalen voorraadgegevens
$voorraadData = $db->Query("SELECT id, artikel_id, locatie, aantal, status_id FROM voorraad");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="kringloop_centrum_duurzaam/assets/css/style.css">
    <script src=""></script>
    <title>Voorraad</title>
</head>
<body>
    <!-- Voorraad tabel -->
    <div class="container">
        <div class="searchbar">
            <input type="text" id="searchInput" placeholder="Zoeken...">
        </div>
        <h2>Voorraad</h2>
            <button class="btn" href="#">Nieuwe spullen toevoegen</button>
        <table class="table">
            <thead>
                <tr>
                    <th><input type="checkbox"></th>
                    <th>ID</th>
                    <th>Artikel ID</th>
                    <th>Aantal</th>
                    <th>Status ID</th>
                </tr>
            </thead>
            <tbody>
                <!-- Voorraadgegevens weergeven -->
                <?php foreach ($voorraadData as $row): ?>
                    <tr>
                    <td><input type="checkbox"></td>
                    <td><?= htmlspecialchars($row['id']); ?></td>
                    <td><?= htmlspecialchars($row['artikel_id']); ?></td>
                    <td><?= htmlspecialchars($row['aantal']); ?></td>
                    <td><?= htmlspecialchars($row['locatie']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
