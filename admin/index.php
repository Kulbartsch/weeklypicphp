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
    <?PHP  include '../src/filestore.php';  ?>

    <h2>Aktionen</h2>
    <ul>
      <li><a href="user.php"        >Bearbeiten der Benutzer-Datei</a></li>
      <li><a href="files_check.php" >Bilder prüfen (<?PHP echo number_of_files_to_check(); ?>)</a></li>
      <li><a href="usagelog.php?lines=300">Anzeige des Nutzungs-Logs</a></li>
      <li><a href="slack.php"   >Senden einer Testnachricht des WeeklyPic Bots in den Slack Channel #weeklypic-adm-2</a></li>
      <!-- li><a href="slack1.php"  >Senden einer Nachricht des WeeklyPic Bots an einen Channel</a></li -->
    </ul>


    <h2>Statistik</h2> 
    <?PHP // TODO: Genertate statistic table dynamically ?>
    <table>
      <tr><td><a href="wepistat_2022.html">Statistik 2022</a></td><td><a href="wepistat_2022.csv">(als CSV)</a></td><td><a href="stat_generate.php?year=2022">aktualisieren</a></td></tr>
      <tr><td><a href="wepistat_2021.html">Statistik 2021</a></td><td><a href="wepistat_2021.csv">(als CSV)</a></td><td></td></tr>
    </table>
    <details>
        <summary>Hinweis! Sollten nach dem "Aktualisieren" der Statistik noch alte Werte angezeigt werden ...</summary>
        <p>
            ... solltest du, auf der Statistik Seite(!), den Cache aktualisieren; auch "Hard Refresh" genannt.
            Das geht wie folgt:
        </p>
        <ul>
            <li>Chrome, Firefox, der Edge unter Windows: Drücke STRG+F5 (Wenn das nicht funktiert versuchte Shift+F5 or STRG+Shift+R).</li>
            <li>Chrome or Firefox unter macOS: Drücke Shift+Command+R.</li>
            <li>Safari unter macOS: Hier gibt es keine einfache Tastenkombination für einen Hard Refresh.
                Drücke Command+Option+E um den Cache zu leern,
                dann halte die Shift Taste gedrückt und klicke auf "Neu Laden" in der Symbolleiste.</li>
        </ul>

    </details>
    <p></p>


    <h2>Freier Speicher auf dem Server</h2>
    <?php // TODO: replace with PHP functions disk_free_space() and disk_total_space() ?>
    <?php
      $one_mb = 1024 * 1024;
      $disk_total_space  = intval( disk_total_space('.') / $one_mb );
      $disk_free_space   = intval( disk_free_space('.') / $one_mb );
      $disk_used_percent = intval( ($disk_total_space - $disk_free_space) / ( $disk_total_space / 100 ) );
    ?>
    <table border="0">
      <tr><td>Gesamte Speicherkapazität:</td><td align="right"><?=  $disk_total_space ?></td><td>MB</td></tr>
      <tr><td>Freie Speicherkapazität:</td><td align="right"><?=  $disk_free_space ?></td><td>MB</td></tr>
      <tr><td>Speicherbelegung:</td><td align="right"><?=  $disk_used_percent ?></td><td>%</td></tr>
    </table>
    <?php
      // setlocale(LC_CTYPE, 'en_US.UTF-8');
      // exec('df -h .', $lines, $result);
      // echo '<p>Ergebnis von "df": ' . $result . ' &nbsp; - &nbsp; 0=OK)</p>';
      // echo '<pre>';
      // foreach ($lines as $line) { echo htmlspecialchars($line) . "\n"; }
      // echo '</pre>';
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
