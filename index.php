<!DOCTYPE html>
<html lang="de" xmlns="http://www.w3.org/1999/html">
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
    <h2>Willkommen zum WeeklyPic One-Stop-Foto!</h2>
    <p>Hier kannst du auf einfachem Weg dein Wochen- oder Monatsbild auf
       2000 Pixel (lange Kante) skalieren und deine EXIF Beschreibung als Tag setzen.
       Nach dem Bearbeiten kannst du das Ergebnis direkt auf WeeklyPic.de hochladen.</p>
    <hr />
    <h3>Dein Bild und Eckdaten</h3>

    <?PHP
      // configuration constants and define functions
      include 'src/config.php';
        // PHPStorm IDE hints for variables used in including source file
        /** @var $debugging bool    */
        /** @var $debugging2  bool    */
        /** @var $config_dir  string    */
        /** @var $app_base_dir  string    */
        /** @var $cookie_name string    */
        /** @var $cookie_split  string    */
        /** @var $cookie_expires  string    */
        /** @var $tag_is_set  string    */
        /** @var $tag_not_set string    */
        /** @var $pushing_pic int   */
        /** @var $push_cloud  int   */
        /** @var $push_filesystem int   */
        /** @var $push_ftp  int   */

        /** @var $upload_server string    */
        /** @var $upload_login  string    */
        /** @var $usage_logging int   */
        /** @var $upload_folder string    */
        /** @var $command_log string    */
        /** @var $usage_log string    */
        /** @var $debug_log string    */
        /** @var $access_log  string    */
        /** @var $user_file string    */
        /** @var $convert_command string    */
        /** @var $exiftool_command  string    */
        /** @var $curl_command  string    */
        /** @var $lc_ctype  string    */
        /** @var $destination_folder  string    */
        /** @var $ftp_exec  string    */
        /** @var $check_dir string    */
        /** @var $check_user  string    */
        /** @var $slack_api_token string    */

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
        log_usage('1I', $val_user, 'cookie used, browser: ' . $_SERVER['HTTP_USER_AGENT'], TRUE, TRUE);
      } else {
        $val_user      = '';
        $val_creator   = '';
        $val_license   = '';
        $val_nogeo     = ' ';
        $val_expert    = ' ';
        $val_usecookie = ' ';
        log_usage('1I', $val_user, 'no cookie, browser: ' . $_SERVER['HTTP_USER_AGENT'], TRUE, TRUE);
      }
      // log_usage('1I', $val_user, '', TRUE, TRUE);

      // set default week and month numbers
      $default_month = date('n');
      $default_week  = date('W');

    ?>

    <p>
      <form action="doit.php" method="post" enctype="multipart/form-data">
        <p>
          <label for="fileToUpload">Bild-Datei auswählen (max. 100 MB):</label><br/>
          <input type="file" name="fileToUpload" id="fileToUpload" max-size="1100000" required>
        </p>
        <p>
            <label for="user">WeeklyPic-Benutzername 🍪:<br/></label>
            <input type="text" id="user" name="user" value="<?= $val_user ?>" required><br/>
            <label for="description">Bildbeschreibung (wird von WeeklyPic genutzt, optional):<br/></label>
            <input type="text" id="description" name="description">
        </p>
        <p>
            Bild-Zeitraum (für den Dateinamen):<br>
            <input type="radio" id="timeframe_w" name="timeframe" value="Woche" checked required>
              <label for="timeframe_w">Woche</label>
              <input type="number" id="week_number" name="week_number" min="1" max="53" step="1=" value="<?= $default_week ?>"><br/>
            <input type="radio" id="timeframe_m" name="timeframe" value="Monat" required>
              <label for="timeframe_m">Monat</label>
              <input type="number" id="month_number" name="month_number" min="1" max="12" step="1=" value="<?= $default_month ?>"><br/>
        </p>
        <p>
          Urheber 🍪 (optional):<br/>
          <input type="text" id="creator" name="creator" value="<?= $val_creator ?>"><br/>
          Lizenz 🍪 (optional):<br/>
          <input type="text" id="license" name="license" value="<?= $val_license ?>"><br/>
        </p>
        <p>
            Geo-Daten im Bild 🍪:<br/>
            <input type="checkbox" id="nogeo" name="nogeo" value="nogeo" <?= $val_nogeo ?>><label for="nogeo"> GPS-Daten löschen</label><br/>
        </p>
        <!-- p>
            Einfacher One-Stop-Foto Upload 🍪:<br/>
            <input type="checkbox" id="onestop" name="onestop" value="onestop" <?= $val_onestop ?>><label for="onestop"> Nach der Anpassung des Bildes, dieses ohne Rückfrage, sofort für WeeklyPic bereitstellen.
            Das funktioniert nur, wenn bei der Anpassung und Prüfung des Bildes keine Probleme aufgetreten sind.</label><br/>
        </p>
        <p>
            Manuelle Prüfung des Bildes vor Bereitstellung für WeeklyPic 🍪:<br/>
            <input type="checkbox" id="checkpic" name="checkpic" value="checkpic" <?= $val_checkpic ?>><label for="onestop"> Bild nach der Anpassung kontrollieren.</label><br/>
        </p -->
        <!-- p>
          Expertenmodus 🍪:<br/>
          <input type="checkbox" id="expert" name="expert" value="expert" <?= $val_expert ?>> Keine Metadaten ändern, Bild nur prüfen und hochladen.<br>
          Du musst allerdings deinen WeeklyPic-Benutzernamen und den Bild-Zeitraum angeben. Die anderen Felder werden ignoriert.
        </p -->
        <input type="checkbox" id="expert" name="expert" value="expert" hidden>
        <p>
          <input type="checkbox" id="usecookie" name="usecookie" value="usecookie" <?= $val_usecookie ?> ><label for="usecookie"> Nutze ein Cookie für deine 🍪-Daten.</label>
        </p>
        <p>
          <input type="submit" value="Bild hochladen und bearbeiten" name="submit">
        </p>
      </form>
    </p>

    <hr />
    <h3>Disclaimer</h3>
    <p>Zugriffe auf die Seite werden protokolliert.
       Wenn bei der Bildbearbeitung Fehler auftreten werden die Fehlermeldungen zu Analyse Zwecken protokolliert.<br />
       Bei Problemen wende dich bitte an die Entwickler im WeeklyPic-Slack-Channel #entwickler-talk.<br />
       Für die Funktionalität und die Verfügbarkeit wird weder Garantie noch Haftung übernommen.</p>
    <hr />
    <p>Dieses Programm ist unter der GNU AFFERO GENERAL PUBLIC LICENSE, Version 3 lizenziert.
       Den Quellcode findest du auf <a href="https://github.com/Kulbartsch/weeklypicphp">GitHub</a>.<br />
       Copyright © 2021 <a href="http://kulbartsch.de/">Alexander Kulbartsch.</a></p>

  </body>
</html>
