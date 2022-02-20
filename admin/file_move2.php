<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>WeeklyPic-One-Stop-Foto Admin - Bild akzeptieren</title>
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

        table {
            border: 1px;
        }

        th {
            text-align: left;
        }
    </style>
</head>
<body>

<h1>Bild akzeptieren</h1>

<?php
include '../src/functions.php';
include '../src/filestore.php';

// _POST Var Handling
$file = sanitize_input("file", TRUE);
$accept = sanitize_input('accept', FALSE);
$year = sanitize_input("year", TRUE);
$month = sanitize_input("month_number", FALSE);
$week = sanitize_input("week_number", FALSE);
if ($_POST["timeframe"] == "Monat") {
    $periodtype = 'm';
    $period = leading_zeros($month, 2);
} else {
    $periodtype = 'W';
    $period = leading_zeros($week, 2);
}

if ($_REQUEST['accept'] == 'TRUE') {
    echo '<h1>Bild akzeptieren und verschieben</h1>';
} else {
    echo '<h1>Bild verschieben</h1>';
}

// BUG: Don't move picture into the same directory!!

$file = file_change_extension($file, 'jpg');
echo '<p> Datei ' . reduce_path($file);

if ($_REQUEST['accept'] == 'TRUE') {
    if (delete_comment($file)) {
        echo ' - akzeptiert.</p>';
    } else {
        echo '<br>Ups, da ist was schief gelaufen. (Entwickler informieren.)</p>';
    }
} else {
    echo '</p>';
}

if (move_picture_set($file, $year, $periodtype, $period)) {
    echo '<p>✅ Verschoben.</p>';
} else {
    echo '<br>⚠️ Ups, da ist was schief gelaufen. (Entwickler informieren.)</p>';
}
?>

<br/>
<p>Hier geht es <a href="index.php">zurück</a>.</p>
<p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im
    WeeklyPic-Slack-Channel #entwickler-talk.</p>

</body>
</html>
