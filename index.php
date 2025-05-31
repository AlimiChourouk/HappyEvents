<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require('db.php');

function getUniqueDates($pdo) {
    $sql = "SELECT DISTINCT dateEvent FROM edition ORDER BY dateEvent ASC";
    $stm = $pdo->query($sql);
    return $stm->fetchAll(PDO::FETCH_COLUMN);
}

function getAllEvents($pdo) {
    $sql = "SELECT 
        ed.editionId,
        ed.dateEvent,
        ed.timeEvent,
        ed.NumSalle,
        ed.image,
        ev.eventId,
        ev.eventType,
        ev.eventTitle,
        ev.eventDescription,
        ev.TariffNormal,
        ev.TariffReduit,
        s.capSalle
    FROM edition ed
    INNER JOIN evenement ev ON ed.eventId = ev.eventId
    INNER JOIN salle s ON ed.NumSalle = s.NumSalle";
    $stm = $pdo->query($sql);
    return $stm->fetchAll(PDO::FETCH_ASSOC);
}

function getFilteredEvents($pdo, $dateDebut = null, $dateFin = null, $categorie = null, $searchTitle = null) {
    $sql = "SELECT 
        ed.editionId,
        ed.dateEvent,
        ed.timeEvent,
        ed.NumSalle,
        ed.image,
        ev.eventId,
        ev.eventType,
        ev.eventTitle,
        ev.eventDescription,
        ev.TariffNormal,
        ev.TariffReduit,
        s.capSalle
    FROM edition ed
    INNER JOIN evenement ev ON ed.eventId = ev.eventId
    INNER JOIN salle s ON ed.NumSalle = s.NumSalle
    WHERE 1=1";
    $params = [];
    if ($dateDebut && $dateFin && $dateDebut !== 'all' && $dateFin !== 'all') {
        $sql .= " AND ed.dateEvent BETWEEN :dateDebut AND :dateFin";
        $params[':dateDebut'] = $dateDebut;
        $params[':dateFin'] = $dateFin;
    }
    if ($categorie && $categorie !== 'all') {
        $sql .= " AND ev.eventType = :categorie";
        $params[':categorie'] = $categorie;
    }
    if ($searchTitle) {
        $sql .= " AND ev.eventTitle LIKE :searchTitle";
        $params[':searchTitle'] = "%" . $searchTitle . "%";
    }
    $stm = $pdo->prepare($sql);
    $stm->execute($params);
    return $stm->fetchAll(PDO::FETCH_ASSOC);
}

try {
    $dates = getUniqueDates($pdo);
    $categories = ['all' => 'All', 'Musique' => 'Music', 'Théâtre' => 'Theater', 'Cinéma' => 'Cinema', 'Rencontres' => 'Meetings'];
    $dateDebut = isset($_GET['dateDebut']) ? $_GET['dateDebut'] : null;
    $dateFin = isset($_GET['dateFin']) ? $_GET['dateFin'] : null;
    $categorie = isset($_GET['categorie']) ? $_GET['categorie'] : null;
    $searchTitle = isset($_GET['searchTitle']) ? $_GET['searchTitle'] : null;

    if ($dateDebut || $dateFin || $categorie || $searchTitle) {
        $events = getFilteredEvents($pdo, $dateDebut, $dateFin, $categorie, $searchTitle);
    } else {
        $events = getAllEvents($pdo);
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css"?v=<?=time();?>>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&display=swap" rel="stylesheet">
    <title>Event List</title>
</head>
<body>
    <header class="header">
        <div class="header-content">
            <a href="index.php" class="logo">
                <div class="logo-icon">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 3.5a.5.5 0 0 0-1 0V9a.5.5 0 0 0 .252.434l3.5 2a.5.5 0 0 0 .496-.868L8 8.71V3.5z"/>
                        <path d="M8 16A8 8 0 1 0 8 0a8 8 0 0 0 0 16zm7-8A7 7 0 1 1 1 8a7 7 0 0 1 14 0z"/>
                    </svg>
                </div>
                <span class="logo-text">EventHub</span>
            </a>
            <nav class="nav-menu">
                <a href="index.php" class="nav-link">Events</a>
                <a href="#" class="nav-link">Categories</a>
                <a href="#" class="nav-link">About</a>
                <a href="#" class="nav-link">Contact</a>
            </nav>
            <div class="auth-buttons">
                <a href="login.php" class="btn login-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M10 3.5a.5.5 0 0 0-.5-.5h-8a.5.5 0 0 0-.5.5v9a.5.5 0 0 0 .5.5h8a.5.5 0 0 0 .5-.5v-2a.5.5 0 0 1 1 0v2A1.5 1.5 0 0 1 9.5 14h-8A1.5 1.5 0 0 1 0 12.5v-9A1.5 1.5 0 0 1 1.5 2h8A1.5 1.5 0 0 1 11 3.5v2a.5.5 0 0 1-1 0v-2z"/>
                        <path fill-rule="evenodd" d="M4.146 8.354a.5.5 0 0 1 0-.708l3-3a.5.5 0 1 1 .708.708L5.707 7.5H14.5a.5.5 0 0 1 0 1H5.707l2.147 2.146a.5.5 0 0 1-.708.708l-3-3z"/>
                    </svg>
                    Login
                </a>
                <a href="register.php" class="btn signup-btn">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M8 8a3 3 0 1 0 0-6 3 3 0 0 0 0 6zm2-3a2 2 0 1 1-4 0 2 2 0 0 1 4 0zm4 8c0 1-1 1-1 1H3s-1 0-1-1 1-4 6-4 6 3 6 4zm-1-.004c-.001-.246-.154-.986-.832-1.664C11.516 10.68 10.289 10 8 10c-2.29 0-3.516.68-4.168 1.332-.678.678-.83 1.418-.832 1.664h10z"/>
                    </svg>
                    Sign Up
                </a>
            </div>
        </div>
    </header>

   
    
    <div class="container">
        <h1>Event List</h1>
       
        <div class="filters">
            <form method="GET" action="">
                <label for="searchTitle">Search by title:</label>
                <input type="text" id="searchTitle" name="searchTitle" 
                       value="<?= htmlspecialchars(isset($_GET['searchTitle']) ? $_GET['searchTitle'] : '') ?>"
                       placeholder="Enter a title...">

                <label for="dateDebut">Start date:</label>
                <select id="dateDebut" name="dateDebut">
                    <option value="all" <?= $dateDebut === 'all' || !$dateDebut ? 'selected' : '' ?>>All</option>
                    <?php foreach ($dates as $date) : ?>
                        <option value="<?= $date ?>" <?= $dateDebut === $date ? 'selected' : '' ?>><?= $date ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="dateFin">End date:</label>
                <select id="dateFin" name="dateFin">
                    <option value="all" <?= $dateFin === 'all' || !$dateFin ? 'selected' : '' ?>>All</option>
                    <?php foreach ($dates as $date) : ?>
                        <option value="<?= $date ?>" <?= $dateFin === $date ? 'selected' : '' ?>><?= $date ?></option>
                    <?php endforeach; ?>
                </select>

                <label for="categorie">Category:</label>
                <select id="categorie" name="categorie">
                    <?php foreach ($categories as $value => $label) : ?>
                        <option value="<?= $value ?>" <?= $categorie === $value ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>

                <input type="submit" value="Filter">
            </form>
        </div>

        <?php if (empty($events)) : ?>
            <div class="no-events">
                <svg xmlns="http://www.w3.org/2000/svg" width="48" height="48" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M7.002 11a1 1 0 1 1 2 0 1 1 0 0 1-2 0zM7.1 4.995a.905.905 0 1 1 1.8 0l-.35 3.507a.552.552 0 0 1-1.1 0L7.1 4.995z"/>
                </svg>
                <p>No events found.</p>
            </div>
        <?php else : ?>
            <div class="events-grid">
                <?php foreach ($events as $event) : ?>
                    <div class="event">
                        <h2><?= htmlspecialchars($event['eventTitle']) ?></h2>
                        <img src="<?= htmlspecialchars($event['image']) ?>" alt="Event image">
                        <div class="event-date">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                                <path d="M11 6.5a.5.5 0 0 1 .5-.5h1a.5.5 0 0 1 .5.5v1a.5.5 0 0 1-.5.5h-1a.5.5 0 0 1-.5-.5v-1z"/>
                                <path d="M3.5 0a.5.5 0 0 1 .5.5V1h8V.5a.5.5 0 0 1 1 0V1h1a2 2 0 0 1 2 2v11a2 2 0 0 1-2 2H2a2 2 0 0 1-2-2V3a2 2 0 0 1 2-2h1V.5a.5.5 0 0 1 .5-.5zM1 4v10a1 1 0 0 0 1 1h12a1 1 0 0 0 1-1V4H1z"/>
                            </svg>
                            <p><strong>Date:</strong> <?= htmlspecialchars($event['dateEvent']) ?></p>
                        </div>
                        <p><strong>Category:</strong> <?= htmlspecialchars($event['eventType']) ?></p>
                        <form action="details.php" method="POST">
                            <button type="submit" name="editionId" value="<?= htmlspecialchars($event['editionId']) ?>">Buy Now</button>
                        </form>
                        <hr style="margin: 15px 0; border: none; border-top: 1px solid rgba(102, 126, 234, 0.2);">
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>