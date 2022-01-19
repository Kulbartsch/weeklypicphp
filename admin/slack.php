<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WeeklyPic-One-Stop-Foto Admin - Slack-Test</title>
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

  	<h1>Senden einer Test-Nachricht des WeeklyPic Bots in den Slack Channel #weeklypic-adm-2</h1>

    <?php
      include '../src/config.php';
      include '../src/slack.php';
      
      echo '<p>Testnachricht gesendet.<br/>'; 
      echo 'Ergebnis: ' . slack('Das ist eine Testnachricht des WeeklyPic Bots.', '#weeklypic-adm');
      echo '</p>';
    ?>

    <br />
    <p>Hier geht es <a href="index.php">zurück</a>.</p>
    <p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im WeeklyPic-Slack-Channel #entwickler-talk.</p>

  </body>
</html>
