<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WeeklyPic-One-Stop-Foto</title>
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
      $usage_log = "../_log/usage.log";
      setlocale(LC_CTYPE, 'en_US.UTF-8');
      // more functions
      include '../src/functions.php';
      // check for file
      if(file_exists($usage_log) == FALSE){
        cancel_processing("Missing usage_log file.");
      }
      // read usage_log  
      exec('tail -n 300 ' . $usage_log, $lines, $result);
      echo '<p>Es werden die letzten 300 Zeilen angezeigt. (Ergebnis von "tail": ' . $result . ' &nbsp; - &nbsp; 0=OK)</p>'
    ?>

<pre>
<?PHP foreach ($lines as $line) { echo htmlspecialchars($line) . "\n"; } ?>
</pre>

    <p>Das Format der Log-Datei ist wie folgt:</p>
    <ul>
      <li>Eine Zeile kann aus mehreren Feldern bestehen, welche durch ein ";" getrennt sind.</li>
      <li>Das erste Feld ist ein Zeitstempel. (In Lokaler Zeit mit der Abweichung zu UTC.)</li>
      <li>Das zweite Feld besteht aus zwei Zeichen: 
          <br/>Das erste Zeichen ist eine Ziffer von 1-3 für die in der Reihenfolge üblicherweise
          aufgerufenen Seiten (1=Startseite=index.php, 2=Bearbeitungsseite=doit.php, 3=Fertig-Seite=final.php). 
          Sollte dort ein "-" stehen kommt der Eintrag aus einer Funktion welche von verschiedenen Seiten aufgerufen werden konnte.
          <br/>Das zweite Zeichen ist entweder eine "E" für einen Fehler (Error), ein "A" für einen Abbruch in der Verarbeitung, ein "W" für eine Warnung, oder eine "I" für eine Information.
          <br/>Warnungen sind die nicht erfolgreichen Bildprüfungen, die nur den Upload in das "check" Verzeichnis erlauben.</li>
      <li>Das dritte Feld ist - sofern gefüllt - der User-Name. (Die Erfassung des Names kann abgeschaltet sein.)</li>
      <li>Das vierte Feld enthält weitere Infos.</li>
    </ul>

    <br />
    <p>Hier geht es <a href="index.php">zurück</a>.</p>
    <p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im WeeklyPic-Slack-Channel #entwickler-talk.</p>

  </body>
</html>
