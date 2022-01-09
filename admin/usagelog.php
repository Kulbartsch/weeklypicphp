<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WeeklyPic-One-Stop-Foto Admin - Usage-Log</title>
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

  	<h1>Anzeige des Nutzungs-Logs</h1>

    <?php
      // configuration constants
      // include '../src/config.php';
      // TODO: use config file
      $usage_log = "../_log/usage.log";
      setlocale(LC_CTYPE, 'en_US.UTF-8');
      // more functions
      include '../src/functions.php';
      // check for file
      if(file_exists($usage_log) == FALSE){
        cancel_processing("Missing usage_log file.");
      }
      $nl = intval($_GET['lines']);
      if($nl < 10) { 
        $nl = 300; 
        echo '<p>⚠️ Warnung, es werden nicht weniger als 10 Zeilen angezeigt. Die Ausgabe wurde auf ' . $nl . ' gesetzt.</p>';
      }
      if($nl > 10000) { 
        $nl = 300; 
        echo '<p>⚠️ Warnung, es werden nicht mehr als 10.000 Zeilen angezeigt. Die Ausgabe wurde auf ' . $nl . ' gesetzt.</p>';

      }
      // read usage_log  
      exec('tail -n ' . $nl . ' ' . $usage_log, $lines, $result);
      echo '<p>Es werden die letzten ' . $nl . ' Zeilen angezeigt. (Ergebnis von "tail": ' . $result . ' &nbsp; - &nbsp; 0=OK)</p>'
    ?>

<pre>
<?PHP foreach ($lines as $line) { echo htmlspecialchars($line) . "\n"; } ?>
</pre>

    <p>Das Format der Log-Datei ist wie folgt:</p>
    <ul>
      <li>Eine Zeile besteht aus mehreren Feldern, welche durch ein ";" getrennt sind.</li>
      <li>Das erste Feld ist ein Zeitstempel. (In lokaler Zeit mit der Abweichung zu UTC.)</li>
      <li>Das zweite Feld besteht aus zwei Zeichen: 
          <br/>Das erste Zeichen ist eine Ziffer von 1 bis 3 für die in der Reihenfolge üblicherweise
          aufgerufenen Seiten (1=Startseite=index.php, 2=Bearbeitungsseite=doit.php, 3=Fertig-Seite=final.php). 
          Sollte dort ein "-" stehen kommt der Eintrag aus einer Funktion welche von verschiedenen Seiten aufgerufen werden konnte.
          <br/>Das zweite Zeichen ist entweder eine "E" für einen Fehler (Error), ein "A" für einen Abbruch in der Verarbeitung, ein "W" für eine Warnung, oder eine "I" für eine Information.
          <br/>Warnungen sind die nicht erfolgreichen Bildprüfungen, die nur den Upload in das "check" Verzeichnis erlauben.</li>
      <li>Das dritte Feld ist - sofern gefüllt - der User-Name. (Die Erfassung des Names kann abgeschaltet sein.)</li>
      <li>Das vierte Feld enthält weitere Informationen.</li>
    </ul>

    <br />
    <p>Hier geht es <a href="index.php">zurück</a>.</p>
    <p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im WeeklyPic-Slack-Channel #entwickler-talk.</p>

  </body>
</html>
