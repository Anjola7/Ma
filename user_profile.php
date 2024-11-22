<?php
session_start();

// Kontrollo sesionin për të siguruar që një përdorues është i kyçur dhe ka rol user
if (!isset($_SESSION['username']) || $_SESSION['role'] !== 'user') {
    header("Location: login.php");
    exit;
}

// Përfshij skedarin e lidhjes me bazën e të dhënave
include 'db.php';

// Merr username-in dhe id e përdoruesit nga sesioni
$username = $_SESSION['username'];
$user_id = $_SESSION['user_id'];

// Query për të marrë të dhënat e përdoruesit specifik
$sql = "SELECT u.username, u.password, u.role, m.username as manager_username
        FROM users u
        LEFT JOIN users m ON u.manager_id = m.id
        WHERE u.id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

// Mbyll lidhjen me bazën e të dhënave
$stmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background: linear-gradient(to right, #d2bcb2, #e2ceb4); /* Gradiente të buta */
            margin: 0;
            padding: 0;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            overflow: hidden;
        }

        /* Dekorative circles in background */
        .background {
            position: absolute;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: -1;
        }

        .circle {
            position: absolute;
            background-color: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            animation: move 20s infinite ease-in-out;
        }

        .circle:nth-child(1) {
            width: 150px;
            height: 150px;
            top: 10%;
            left: 5%;
            animation-duration: 25s;
        }

        .circle:nth-child(2) {
            width: 200px;
            height: 200px;
            top: 50%;
            left: 80%;
            animation-duration: 18s;
        }

        .circle:nth-child(3) {
            width: 300px;
            height: 300px;
            top: 80%;
            left: 20%;
            animation-duration: 22s;
        }

        @keyframes move {
            0% { transform: translate(0, 0); }
            50% { transform: translate(50px, 50px); }
            100% { transform: translate(0, 0); }
        }

        /* Container dhe animacionet */
        .container {
            background-color: rgba(243, 242, 238, 0.7); /* Ngjyra e sfondit të container-it */
            padding: 60px; /* Rritur për më shumë hapësirë */
            border-radius: 20px; /* Rritur për pamje më të bukur */
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3); /* Rritur për efekt më të fortë */
            text-align: center;
            width: 90%; /* Për më shumë hapësirë */
            max-width: 1200px; /* Kufizohet në një gjerësi maksimale për të mbajtur dizajnin më të menaxhueshëm */
            opacity: 0;
            animation: fadeIn 2s forwards;
            margin: 0 auto; /* Qendron në qendër të faqes */
        }

        @keyframes fadeIn {
            to { opacity: 1; }
        }

        h1 {
            margin-bottom: 30px;
            font-size: 36px; /* Rritur për më shumë vizibilitet */
            color: #333;
            text-transform: uppercase;
            animation: slideDown 1s forwards;
        }

        @keyframes slideDown {
            from { transform: translateY(-50px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        h2 {
            margin-bottom: 20px;
            font-size: 28px;
            color: #333;
        }

        label {
            display: block;
            text-align: left;
            margin: 15px 0 5px;
            font-weight: bold;
            color: #333;
        }

        input[type="text"],
        input[type="password"] {
            width: 100%;
            padding: 15px;
            margin-bottom: 25px; /* Rritur për më shumë hapësirë */
            border: 1px solid #ccc;
            border-radius: 5px;
            box-sizing: border-box;
            transition: border-color 0.3s ease;
        }

        input[type="text"]:focus,
        input[type="password"]:focus {
            border-color: #333;
        }

        .button {
            width: 100%;
            padding: 15px;
            background-color: #f0dfdf; /* Ngjyra më e lehtë për butonin */
            color: #333; /* Ngjyra e tekstit në butonin */
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 20px; /* Rritur për më shumë vizibilitet */
            transition: background-color 0.3s ease, transform 0.3s ease;
            animation: pulse 2s infinite;
        }

        .button:hover {
            background-color: #e2ceb4; /* Ngjyra më e lehtë për hover në butonin */
            transform: translateY(-3px);
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        a.button {
            display: block;
            text-decoration: none;
            text-align: center;
            margin-top: 20px;
            font-size: 18px; /* Rritur për më shumë vizibilitet */
            color: #333;
        }

        a.button:hover {
            text-decoration: underline;
        }

        /* Stili për tabelën */
        .table-container {
            width: 90%;
            max-width: 1200px; /* Kufizohet në një gjerësi maksimale për të mbajtur dizajnin më të menaxhueshëm */
            margin: 0 auto; /* Qendron në qendër të faqes */
        }

        table {
            border-collapse: collapse;
            width: 100%; /* E zgjeron tabelën në të gjithë gjerësinë e container-it */
            background-color: #ffffff; /* Ngjyra e sfondit të tabelës */
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        table, th, td {
            border: 1px solid #ddd; /* Ngjyra e kufijve të tabelës */
        }

        th, td {
            padding: 15px; /* Rrit padding-un për më shumë hapësirë në qeliza */
            text-align: left;
        }

        th {
            background-color: #9c8383; /* Ngjyra e header të tabelës */
            color: white; /* Ngjyra e tekstit në header të tabelës */
        }

        tr:nth-child(even) {
            background-color: #f2f2f2; /* Ngjyra e sfondit për rreshtat e barabartë */
        }

        tr:hover {
            background-color: #dff9fb; /* Ngjyra e sfondit për hover në rreshta */
        }

        /* Stili për mesazhin e mirëseardhjes */
        .welcome-message {
            width: 100%;
            text-align: center;
            padding: 20px;
            background-color: #f0dfdf; /* Ngjyra e sfondit të mesazhit të mirëseardhjes */
            color: #333; /* Ngjyra e tekstit në mesazhin e mirëseardhjes */
            font-size: 24px;
            font-weight: bold;
            border-radius: 5px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="background">
        <!-- Dekorative circles in background -->
        <div class="circle"></div>
        <div class="circle"></div>
        <div class="circle"></div>
    </div>
    <div class="container">
        <header>
            <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?> (User)</h1>
        </header>
        <h2>Your Profile</h2>
        <?php if ($row = $result->fetch_assoc()): ?>
        <div class="table-container">
            <table>
                <tr>
                    <th>Username</th>
                    <td><?php echo htmlspecialchars($row['username']); ?></td>
                </tr>
                <tr>
                    <th>Role</th>
                    <td><?php echo htmlspecialchars($row['role']); ?></td>
                </tr>
                <tr>
                    <th>Manager</th>
                    <td><?php echo htmlspecialchars($row['manager_username']); ?></td>
                </tr>
            </table>
        </div>
        <?php else: ?>
            <p>No user data found.</p>
        <?php endif; ?>
    </div>
</body>
</html>
