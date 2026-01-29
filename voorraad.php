// Auteur : Armandas Rakevicius
// Inhoud : Logica voor het voorraadbeheer
// Laatst bijgewerkt : 29 - 01 - 2026
<?php
// Dit bestand toont een lijst met spullen op voorraad en een formulier om nieuwe artikelen toe te voegen

// Sidebar invoegen
include 'kringloop_centrum_duurzaam/includes/sidebar.html';
// Database config toevoegen om $pdo te krijgen
include 'kringloop_centrum_duurzaam/config/database.php';

class Voorraad {
    public $pdo; // PDO publiciabele aanmaken om database te gebruiken 

    // constructor om PDO te initialiseren
    function __construct($pdo) {
        $this->pdo = $pdo;
    }

    // getAll haalt alle voorraad details die nodig zijn op uit de database
    function getAll() {
        // JOIN tussen voorraad en artikel om omschrijving te krijgen
        $stmt = $this->pdo->query("SELECT v.id, a.naam AS omschrijving, v.aantal FROM voorraad v JOIN artikel a ON v.artikel_id = a.id ORDER BY v.id ASC");
        return $stmt->fetchAll(PDO::FETCH_ASSOC); // geeft array terug
    }
} 

// Voorraad object aanmaken om deze vervolgens te gebruiken
$voorraad = new Voorraad($pdo);

class Artikel {
    public $pdo; // PDO publiciabele aanmaken om database te gebruiken

    function __construct($pdo) {
        $this->pdo = $pdo;
    } // PDO initialiseren
    
    //functie om een nieuw artikel toe te voegen aan de database
    function add($categorie_id, $naam, $prijs_ex_btw, $aantal) {
        // voeg toe aan de artikel tabel
        $stmt = $this->pdo->prepare("INSERT INTO artikel (categorie_id, naam, prijs_ex_btw) VALUES (?, ?, ?)");
        $stmt->execute([$categorie_id, $naam, $prijs_ex_btw]);
        $artikel_id = $this->pdo->lastInsertId();

        // voeg toe aan de voorraad tabel
        $stmt = $this->pdo->prepare("INSERT INTO voorraad (artikel_id, locatie, aantal, status_id, ingeboekt_op) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$artikel_id, 'Magazijn', $aantal, 1, date('Y-m-d H:i:s')]);

        return $artikel_id;
    }
    // Haal alle categorieën op uit de categorie tabel
    function getAllCategories() {
        $stmt = $this->pdo->query("SELECT id, naam FROM categorie");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);//geeft array terug
    }
}

// Maak artikel object aan
$artikel = new Artikel($pdo);

// POST handler als het formulier is verzonden lees velden en roep add() aan
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['addArtikel'])) {
    $artikel->add(
        $_POST['categorie_id'],
        $_POST['naam'],       
        $_POST['prijs_ex_btw'],  
        $_POST['aantal']
    );
    //redirect naar de voorraad pagina
    header('Location: voorraad.php');
    exit;
}

// Haal categorieën voor het formulier op 
$categories = $artikel->getAllCategories();

// Haal alle voorraad data op om te tonen in de tabel
$voorraadData = $voorraad->getAll();
?>
<!DOCTYPE html>
<html lang="nl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Voorraad</title>
    <link rel="stylesheet" href="kringloop_centrum_duurzaam/assets/css/style.css">
</head>
<body>
<div class="searchbar">
    <input type="text" id="searchInput" placeholder="Zoeken..." onkeyup="filterTable()">
</div>

<h2>Voorraad</h2>
<button class="btn" onclick="toggleForm()">Nieuwe Artikel</button>

<!-- formulier om een nieuw artikel te maken -->
<div id="addForm">
    <form method="POST">
        <!-- keuze uit categorie tabel -->
        <label>Categorie:</label>
        <select name="categorie_id" required>
            <option value="">Selecteer categorie</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['naam']) ?></option>
            <?php endforeach; ?>
        </select>

        <label>Naam Artikel:</label>
        <input type="text" name="naam" required>

        <label>Prijs (excl. BTW):</label>
        <input type="number" step="0.01" name="prijs_ex_btw" required>

        <label>Aantal:</label>
        <input type="number" step="1" min="0" name="aantal" value="0" required>

        <button type="submit" name="addArtikel">Toevoegen</button>
    </form>
</div>

<table id="voorraadTable">
    <thead>
        <tr>
            <th>ID</th>
            <th>Omschrijving</th>
            <th>Hoeveelheid</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($voorraadData as $row): ?>
        <tr>
            <td><?= htmlspecialchars($row['id']) ?></td>
            <td><?= htmlspecialchars($row['omschrijving']) ?></td>
            <td><?= htmlspecialchars($row['aantal']) ?></td>
            <td class="actions">&#8942;</td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<script>
// logica voor zoekbalk 
function filterTable() {
    const input = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('#voorraadTable tbody tr').forEach(row => {
        const text = row.cells[1].textContent.toLowerCase();
        row.style.display = text.includes(input) ? '' : 'none';
    });
}

// logica om formulier te tonen/verbergen
function toggleForm() {
    const form = document.getElementById('addForm');
    form.style.display = form.style.display === 'none' ? 'block' : 'none';
}
</script>

</body>
</html>