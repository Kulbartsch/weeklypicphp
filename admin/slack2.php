<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>WeeklyPic-One-Stop-Foto Admin - Slack-Test</title>
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

<h1>Senden einer Nachricht des WeeklyPic Bots</h1>

<?php
include '../src/config.php';
include '../src/slack.php';

// _POST Var Handling
if (empty($_POST['to']) || empty($_POST['message'])) {
    cancel_processing("Es muss der Empfänger und die Nachricht angegeben werden!");
}
$to = trim($_POST['to']);
$message = trim($_POST['message']);

if (substr($to, 0, 1) != '@' and substr($to, 0, 1) != '#') {
    cancel_processing('Empfänger muss mit @ oder # beginnen.');
}

echo '<p>Testnachricht gesendet.<br/>';
echo 'Ergebnis: ' . slacku($message, $to);
echo '</p>';
?>

<br/>
<p>Hier geht es <a href="index.php">zurück</a>.</p>
<p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im
    WeeklyPic-Slack-Channel #entwickler-talk.</p>

</body>
</html>
