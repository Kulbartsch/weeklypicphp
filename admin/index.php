<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WeeklyPic-One-Stop-Foto Admin</title>
    <style type="text/css">
      body {margin:30px auto; max-width:650px; line-height:1.6; font-size:18px;
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

  	<h1>WeeklyPic-One-Stop-Foto Admin-Seite</h1>

    <h2>Aktionen</h2>
    <ul>
      <li><a href="user.php"    >Bearbeiten der Benutzer-Datei</a></li>
      <li><a href="usagelog.php?lines=300">Anzeige der letzten 300 Zeilen des Nutzungs-Logs</a></li>
        <ul>
          <li><a href="usagelog.php?lines=150">... der letzten 150 Zeilen</a></li>
          <li><a href="usagelog.php?lines=600">... der letzten 600 Zeilen</a></li>
          <li><a href="usagelog.php?lines=1500">... der letzten 1500 Zeilen</a></li>
          <li><a href="usagelog.php?lines=3000">... der letzten 3000 Zeilen</a></li>
        </ul>
      <li><a href="slack.php"   >Senden einer Testnachricht des WeeklyPic Bots in den Slack Channel #weeklypic-adm-2</a></li>
      <!-- li><a href="slack1.php"  >Senden einer Nachricht des WeeklyPic Bots an einen Channel</a></li -->
    </ul>

    <h2>Statistik</h2>
    <ul>
      <li><a href="wepistat_2021.html">Statistik 2021</a> <a href="wepistat_2021.csv">(als CSV)</a></li>
    </ul>
    <p></p>

    <h2>Freier Speicher auf dem Server</h2>
    <?php
      setlocale(LC_CTYPE, 'en_US.UTF-8');
      exec('df -h .', $lines, $result);
      echo '<p>Ergebnis von "df": ' . $result . ' &nbsp; - &nbsp; 0=OK)</p>';
      echo '<pre>';
      foreach ($lines as $line) { echo htmlspecialchars($line) . "\n"; }
      echo '</pre>';
    ?>
    <p></p>
    
    <h2>Weitere Links</h2>
    <ul>
      <li><a href="https://www.weeklypic.de/">WeeklyPic Projektseite</a></li>
      <li><a href="https://wepi.alice-and-bob.de/U9MGw5B4a2pA4tS2h/">WeeklyPic One-Stop-Foto Upload</a></li>
    </ul>
    <p></p>

    <br><hr><br>
    <p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im WeeklyPic-Slack-Channel #entwickler-talk.</p>

  </body>
</html>
