<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>WeeklyPic-One-Stop-Foto Admin - Benutzer speichern</title>
    <style type="text/css">
        body {
            margin: 30px auto;
            max-width: 650px;
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

<h1>Speichern der Benutzer-Liste</h1>

<?php
// configuration constants
// include '../src/config.php';
// BUG: use config file

$user_file = "../_log/user.txt";
setlocale(LC_CTYPE, 'en_US.UTF-8');
// more functions
include '../src/functions.php';
// check for file
if (file_exists($user_file) == FALSE) {
    cancel_processing("Missing user file.");
}
// get the users from parameters
$users_edited = explode("\n", $_REQUEST["users"]);
// echo "<p>users edited:" . print_r($users_edited) . "</p>"; // debug
// deconvert special html chars
foreach ($users_edited as $line) {
    $users_data[] = htmlspecialchars_decode($line);
}
// save file
if (file_put_contents($user_file, implode("\n", $users_data)) == FALSE) {
    echo "<p>⚡️ Fehler beim Speichern der Benutzer-Daten. </p>";
} else {
    echo "<p>✅ Benutzer-Daten wurden gespeichert.</p>";
}
// re-read for validation display
$user_lines = explode(PHP_EOL, file_get_contents($user_file));
?>
<p>Gespeicherte User-Datei:</p>
<pre>
<?PHP foreach ($user_lines as $line) {
    echo htmlspecialchars($line) . "\n";
} ?>
      </pre>

<p>Hier geht es <a href="index.php">zurück</a>.</p>
<p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im
    WeeklyPic-Slack-Channel #entwickler-talk.</p>

</body>
</html>
