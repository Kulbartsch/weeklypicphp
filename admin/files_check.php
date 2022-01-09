<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WeeklyPic-One-Stop-Foto Admin - Bilder prüfen</title>
    <style type="text/css">
      body {margin:5% auto; line-height:1.6; font-size:18px;
        color:#444; padding:0 10px;
        background:#eee;
        -webkit-font-smoothing:antialiased;
        font-family:"Helvetica Neue",Helvetica,Arial,Verdana,sans-serif }
      h1,h2,h3 {line-height:1.2}
      input {font-size:18px}
      pre {border:1px solid; padding:0.5em; border-color:black}
    </style>
  </head>
  <body>

  	<h1>Bilder prüfen</h1>

    <?php
        echo '<table><tr><th>Bild-Datei</th><th>Kommentar</th><th span=3>Aktion</th></tr>' . PHP_EOL;
        $fstc = find_files_to_check();
        foreach($fstc as $ftc) {
            echo '<tr><td>' . $ftc . '</td></tr>' . PHP_EOL;
        } 
        echo '</table>';
    ?>

    <br />
    <p>Hier geht es <a href="index.php">zurück</a>.</p>
    <p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im WeeklyPic-Slack-Channel #entwickler-talk.</p>

  </body>
</html>
