<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>WeeklyPic-One-Stop-Foto Admin - Statistik erstellen</title>
    <style type="text/css">
        body {
            margin: 5% auto;
            line-height: 1.6;
            font-size: 18px;
            color: #444;
            padding: 0 10px;
            background: #eee;
            -webkit-font-smoothing: antialiased;
            font-family: "Helvetica Neue", Helvetica, Arial, Verdana, sans-serif
        }

        h1, h2, h3 {
            line-height: 1.2
        }

        input {
            font-size: 18px
        }

        pre {
            padding: 0.5em;
            border: 1px solid black;
        }
    </style>
</head>
<body>

<h1>Generieren der Statistik</h1>

<?php
$year = $_GET['year'];
$stat_csv = 'wepistat_' . $year . '.csv';
setlocale(LC_CTYPE, 'en_US.UTF-8');

$current_year = intval(date('Y'));
$min_year = $current_year - 1; // Last year
$max_year = $current_year + 1; // Allow next year
if ($year < $min_year || $year > $max_year) {
    echo '<p>⚠️ Fehler, es wurde ein ungültiges Jahr ausgewählt!</p>';
} else {
    exec('./wepistat_fs.sh 2>&1 ' . $year, $lines, $result);
    echo '<p>Fertig. Ergebnis: ' . $result . '</p>';
    echo '<p>Protokoll:</p>';
    echo '<pre>';
    foreach ($lines as $line) {
        echo htmlspecialchars($line) . "\n";
    }
    echo '</pre>';
}
?>

<br/>
<p>Hier geht es <a href="index.php">zurück</a>.</p>
<p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im
    WeeklyPic-Slack-Channel #entwickler-talk.</p>

</body>
</html>
