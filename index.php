<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WeeklyPic-One-Stop-Foto</title>
    <style type="text/css">
      body {margin:30px auto; max-width:650px; line-height:1.6; font-size:18px;
        color:#444; padding:0 10px;
        background:#eee;
        -webkit-font-smoothing:antialiased;
        font-family:"Helvetica Neue",Helvetica,Arial,Verdana,sans-serif }
      h1,h2,h3 {line-height:1.2}
      input {font-size:18px}
    </style>
  </head>
  <body>

  	<h1>Hallo! ❤️</h1>
    <h2>Wilkommen zum WeeklyPic One-Stop-Foto!</h2>
    <p>Hier kannst du auf einfachem Weg dein Wochen- oder Monatsbild auf
       2000 Pixel (lange Kante) skalieren und deine EXIF Beschreibung als Tag setzen.
       Nach dem Bearbeiten kannst du das Ergebnis direkt auf WeeklyPic.de hochladen.
    <hr />
    <h3>Dein Bild und Eckdaten</h3>

    <?PHP
      // configuration constants and define functions
      include 'src/config.php';
      include 'src/functions.php';

      // read cookie storing common values (Weekly-Pic-Name, Creator, license, nogeo)
      if(isset($_COOKIE[$cookie_name])) {
        $cookie_value  = explode( $cookie_split , $_COOKIE[$cookie_name] , 6 );
        $val_user      = $cookie_value[0];
        $val_creator   = $cookie_value[1];
        $val_license   = $cookie_value[2];
        $val_nogeo     = $cookie_value[3]; // 'checked' or empty
        if(array_key_exists(4, $cookie_value)) {
          $val_expert  = $cookie_value[4]; // 'checked' or empty // new 
        } else {
          $val_expert  = ' ';
        }
        $val_usecookie = ' checked ';
        log_usage('1I', $val_user, 'cookie used', TRUE, TRUE);
      } else {
        $val_user      = '';
        $val_creator   = '';
        $val_license   = '';
        $val_nogeo     = ' ';
        $val_expert    = ' ';
        $val_usecookie = ' ';
        log_usage('1I', $val_user, 'no cookie', TRUE, TRUE);
      }
      log_usage('1I', $val_user, '', TRUE, TRUE);

      // set default week and month numbers
      $default_month = date('n');
      $default_week  = date('W');

    ?>

    <p>
      <form action="doit.php" method="post" enctype="multipart/form-data">
        <p>
          Bild-Datei auswählen (max. 30 MB):<br/>
          <input type="file" name="fileToUpload" id="fileToUpload" required>
        </p>
        <p>
          WeeklyPic-Benutzername 🍪:<br/>
          <input type="text" id="user" name="user" value="<?= $val_user ?>" required><br/>
          Bildbeschreibung (wird von WeeklyPic genutzt, optional):<br/>
          <input type="text" id="description" name="description">
        </p>
        <p>
          Bild-Zeitraum (für den Dateinamen):<br>
          <input type="radio" id="timeframe" name="timeframe" value="Woche" checked required>
          Woche <input type="number" name="week_number" min="1" max="52" step="1=" value="<?= $default_week ?>"><br>
          <input type="radio" id="timeframe" name="timeframe" value="Monat" required>
          Monat <input type="number" name="month_number" min="1" max="12" step="1=" value="<?= $default_month ?>"><br/>
        </p>
        <p>
          Urheber 🍪 (optional):<br/>
          <input type="text" id="creator" name="creator" value="<?= $val_creator ?>"><br/>
          Lizenz 🍪 (optional):<br/>
          <input type="text" id="license" name="license" value="<?= $val_license ?>"><br/>
          Geo-Daten im Bild 🍪:</br>
          <input type="checkbox" id="nogeo" name="nogeo" value="nogeo" <?= $val_nogeo ?>> GPS-Daten löschen<br>
        </p>
        <p>
          Expertenmodus 🍪:</br>
          <input type="checkbox" id="expert" name="expert" value="expert" <?= $val_expert ?>> Keine Metadaten ändern, Bild nur prüfen und hochladen.<br>
          Du musst allerdings deinen WeeklyPic-Benutzernamen und den Bild-Zeitraum angeben. Die anderen Felder werden ignoriert.
        </p>
        <p>
          <input type="checkbox" id="usecookie" name="usecookie" value="usecookie" <?= $val_usecookie ?> > Nutze ein Cookie für deine 🍪-Daten.
        </p>
        <p>
          <input type="submit" value="Bild hochladen und bearbeiten" name="submit">
        </p>
      </form>
    </p>

    <hr />
    <h3>Disclaimer</h3>
    <p>Die Bilder, die nicht gelöscht wurden, werden periodisch von Hand gelöscht.<br>
       Zugriffe auf die Seite werden protokolliert.
       Wenn bei der Bildbearbeitung Fehler auftreten werden die Fehlermeldungen zu Analyse Zwecken protokolliert.<br />
       Bei Problemen wende dich bitte an die Entwickler im WeeklyPic-Slack-Channel #entwickler-talk.<br />
       Für die Funktionalität und die Verfügbarkeit wird weder Garantie noch Haftung übernommen.</p>
    <p>Dieses Programm ist unter der GNU AFFERO GENERAL PUBLIC LICENSE, Version 3 lizensiert.
       Den Quellcode findest du auf <a href="https://github.com/Kulbartsch/weeklypicphp">GitHub</a>.</p>

  </body>
</html>
