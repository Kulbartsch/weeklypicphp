<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WeeklyPic-One-Stop-Foto Admin - Bild akzeptieren</title>
    <style type="text/css">
      body {margin:5% auto; line-height:1.6; font-size:18px;
        color:#444; padding:0 10px;
        background:#eee;
        -webkit-font-smoothing:antialiased;
        font-family:"Helvetica Neue",Helvetica,Arial,Verdana,sans-serif }
      h1,h2,h3 {line-height:1.2}
      input {font-size:18px}
      pre {border:1px solid; padding:0.5em; border-color:black}
      table {border: 1px;}
      th {text-align: left;}
    </style>
  </head>
  <body>

  	<h1>Bild akzeptieren</h1>

    <?php
        include '../src/functions.php';
        include '../src/filestore.php';

        echo '<p> Datei ' . reduce_path(file_change_extension($_REQUEST['file'], 'jpg'));
        if( delete_comment($_GET['file'])) {
            echo ' - akzeptiert.</p>';
        } else {
            echo '<br>Ups, da ist was schief gelaufen. (Entwickler informieren.)<p>';
        }
    ?>

    <br />
    <p>Hier geht es <a href="index.php">zurück</a>.</p>
    <p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im WeeklyPic-Slack-Channel #entwickler-talk.</p>

  </body>
</html>
