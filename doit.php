<?PHP
  // configuration constants
  include 'src/config.php';

  include 'src/functions.php';
  include 'src/exif_parsing.php';
  include 'src/user_functions.php';

  // session (must be handled before any html code)
  session_start();

  log_debug('>>>> START doit.php', '');

  // _POST Var Handling
  $user         = sanitize_input("user", TRUE);
  $creator      = sanitize_input("creator", FALSE);
  $license      = sanitize_input("license", FALSE);
  $description  = sanitize_input("description", FALSE);
  $description_isset = FALSE;
  if(array_key_exists("nogeo", $_POST)){
    $no_geo = TRUE;
    $nogeo_cookie = 'checked';
  } else {
    $no_geo = FALSE;
    $nogeo_cookie = ' ';
  }
  if(array_key_exists("expert", $_POST)){
    $expertmode = TRUE;
    $expert_cookie = 'checked';
  } else {
    $expertmode = FALSE;
    $expert_cookie = ' ';
  }

  // cookie (must be handled before any html code)
  // Store common values cookies for next time, if requested
  if(array_key_exists("usecookie", $_POST)) {
    $cookie_value = implode( $cookie_split, array($user, $creator, $license, $nogeo_cookie, $expert_cookie) );
    setcookie($cookie_name, $cookie_value, $cookie_expires, "/");
  } elseif(isset($_COOKIE[$cookie_name])) { // delete cookie if no storing requested (in case there was a cookie before)
    $cookie_value = '';
    setcookie($cookie_name, $cookie_value, 1, "/");
  }

?>
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
      table{ border-collapse: collapse; }
      th, td { padding: 3px;  text-align: left; }
      table, th, td { border: 1px solid black; }
      img { image-orientation: from-image; }
      #drawer1:not(:target) .hideable,
      #drawer1:not(:target) .hide,
      #drawer1:target .show {display: none;}
      #drawer1:target .hideable {display: block;}
      .show, .hide { background: #fff; color: #333; border: 1px solid;
        border-color: #333; border-radius: 4px; padding: 5px;
        text-decoration: none;}
    </style>
  </head>
  <body>

    <?php

      // TODO: reduce primary=usage log to pages 2 and 3, add an access log 
      // TODO: Better message when problems are deteced
      // IDEA: Use bot to inform admins about pictures send to check directory
      // BUG: not all critical messages are logged
      // BUG: Don't upload to too old timeranges
      // BUG: Gross/Kleinschreibung im Titel/Usernamen ignorieren (gerade Expertenmodus)
      // BUG: HTML special chars are converted before they are stored as metadata. That's not ok (check with < and &)
      // BUG: check all variable output if it's converted with htmlspecialchars() 
      // BUG: a not processed upload - i.e. picture is to big - is not detected = no filename
      // IDEA: check for umlaute in requested picture title
      // IDEA: validate if picture is for the *current* week/month (and year) - warn if not
      // IDEA: "Lustige" Nachrichten an die Teilnehmer (im Web oder in den Slack).

      //####################################################################

      // validate user name against DB
      $user_db = load_user();
      log_debug('user_db', $user_db);
      $user_info = get_user($user, $user_db);
      if($user_info == 'not_found'){
        echo "<p>Ich kenne dich nicht. 🤨</p>";
        log_usage('2E', $user, 'User ' . $user . ' unknown');
        // cancel processing when user is unknown
        if($check_user == 'ON') {
          cancel_processing('Unbekanter Teilnehmer: ' . $user);
        }
        $user_called = $user;
      } else {
        $user_called = $user_info["called"];
        log_debug("Username from form", $user);
        $user = $user_info["userid"];  // to bring the case of the name to its default 
        log_debug("Username from user_db", $user);
      }


      //####################################################################

      echo "<h1>Hallo! ❤️</h1>";
      echo "<p>Grüezi " . htmlspecialchars($user_called) . ".</p>";

      if (!empty($description))
      {
        echo "<p>Dein Bild soll also <i>" . htmlspecialchars($description) . "</i> heissen?!</p>";
        $description_isset = true;
      } else {
        log_usage('2I', $user, 'No picture titel given on startpage');
      }

      if($debugging == TRUE) {
        echo "<p>";
        echo "debugging: " . $debugging . '<br/>';
        echo "cookie_value: " . $cookie_value . '<br/>';
        echo "cookie_name: " . $cookie_name  . '<br/>';
        echo "cookie_expires: " . $cookie_expires . '<br/>';
        echo "upload folder: " . $upload_folder . '<br/>';
        echo "command log file: " . $command_log . '<br/>';
        echo "usage log file: " . $usage_log . '<br/>';
        echo "log level: " . $usage_logging . '<br/>';
        echo "convert command: " . $convert_command . '<br/>';
        echo "exiftool command: " . $exiftool_command . '<br/>';
        echo "curl command: " . $curl_command . '<br/>';
        echo '</p>' ;
      }


      //####################################################################
      // generate filename from parameters

      log_usage('2I', $user, $expertmode ? 'Expert' : 'no expert', TRUE, TRUE );
      if(empty($user)) {
        cancel_processing("Fehler! Kein Weekly-Pic-Benutzernamen angegeben.");
      }

      // TODO: also calculate year
      $default_month = date('n');
      $default_week  = date('W');
      $requested_month = validate_number_and_return_string(sanitize_input("month_number", TRUE), 1, 12);
      $requested_week  = validate_number_and_return_string(sanitize_input("week_number", TRUE), 1, 52);
      if($_POST["timeframe"] == "Monat") {
        $filename = 'm_' . $requested_month . '_' . $user ;
        $requested_period_type = 'M';
        $requested_period      = $requested_month;
      } else { // asume Woche
        $filename = 'w_' . $requested_week . '_' . $user ;
        $requested_period_type = 'W';
        $requested_period      = $requested_week;
      }


      //####################################################################
      // File validation and upload handling

      if(array_key_exists("fileToUpload", $_FILES) == FALSE) {
        cancel_processing("Fehler! Das Bild wurde nicht hochgeladen.");
      }
      $fileToUpload  = $_FILES["fileToUpload"];
      $file_basename = basename($fileToUpload["name"]);
      $upload_file   = $upload_folder . $file_basename;
      // $filename      = pathinfo($fileToUpload['name'], PATHINFO_FILENAME);
      if($debugging == true) {
        echo "<p>file name: " .      $filename ;
        echo "<br>file type: " .     $fileToUpload["type"];
        echo "<br>file tmp name: " . $fileToUpload["tmp_name"];
        echo "<br>file basename:     $file_basename";
        echo "<br>log file:          $command_log";
        echo "<br>upload filename:   $upload_file</p>";
      }
      
      // BUG: Check if file exists. There are still situations when it's not detected.
      
      //Überprüfung der Dateiendung
      $extension = strtolower(pathinfo($upload_file, PATHINFO_EXTENSION));
      //$allowed_extensions = array('png', 'jpg', 'jpeg', 'JPG', 'JPEG');
      $allowed_extensions = array('jpg', 'jpeg', 'JPG', 'JPEG');
      if(!in_array($extension, $allowed_extensions)) {
        cancel_processing("Fehler! Ungültige Dateiendung.");
      }

      //Überprüfung der Dateigröße
      $max_size = 32000*1024; //32MB
      if($_FILES['fileToUpload']['size'] > $max_size) {
        cancel_processing("Bitte keine Dateien größer 32 MB hochladen.");
      }

      //Überprüfung dass das Bild keine Fehler enthält
      if(function_exists('exif_imagetype')) { //Die exif_imagetype-Funktion erfordert die exif-Erweiterung auf dem Server
        //$allowed_types = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
        $allowed_types = array(IMAGETYPE_JPEG);
        $detected_type = exif_imagetype($fileToUpload["tmp_name"]);
        if($debugging == true) {
          echo "<p>allowed extensions: ";
            foreach ($allowed_types as $x) echo $x . ", ";
          echo "<br>detected type: " . $detected_type . "</p>";
        }
        if(!in_array($detected_type, $allowed_types)) {
          cancel_processing("Nur der Upload von Bilddateien ist gestattet. Du verwendest $detected_type .");
        }
      }
      else {
        cancel_processing("Fehler! Keine PHP-EXIF functions verfügbar. Bitte Admins informieren!");
      }

      // fix extension
      log_debug('Original extension', $extension);
      $extension = strtolower($extension);
      if($extension == 'jpeg') { $extension = 'jpg'; }
      log_debug('New extension', $extension);

      // Pfad zum Upload
      $new_path = $upload_folder.$filename.'.'.$extension;
      $tmp_file = $upload_folder.$filename.'_tmp.'.$extension;

      // delete existing file(s)
      if(file_exists($new_path)) {
        echo "<p>Bereits vorhandenes Bild wird gelöscht.</p>";
        if(!delete_file($new_path)) {
          cancel_processing("Fehler! Konnte vorhandene Datei nicht löschen. Bitte Admins informieren!");
        }
      }
      if(file_exists($tmp_file)) {
        echo "<p>Bereits vorhandenes Bild (tmp) wird gelöscht.</p>";
        if(!delete_file($tmp_file)) {
          cancel_processing("Fehler! Konnte vorhandene Datei nicht löschen. Bitte Admins informieren!");
        }
      }

      //Alles okay, verschiebe Datei an neuen Pfad
      move_uploaded_file($fileToUpload['tmp_name'], $new_path);
      echo 'Dein Bild ist erfolgreich hier angekommen.'; // : <a href="'.$new_path.'">'.$new_path.'</a>';


      //####################################################################
      // Get title from picture if not given by form

      $exif_data_orig = get_exif_data($new_path);
      if(($description_isset === FALSE) or ($expertmode)) {
        $description = get_any_title($exif_data_orig);
        if($description != '') {
          $description_isset = TRUE;
          if(!$expertmode) {
            echo '<p>Du hast keinen Titel für das Bild auf der Startseite angegeben aber ich habe einen Titel im Bild gefunden, der verwendet wird. (Siehe die Tabelle.)</p>';
          }
          log_usage('2I', $user, 'Got picture titel from picture itself: ' . $description);
        } else {
          if(!$expertmode) {
            echo '<p>❗️❓ Du hast keinen Titel für das Bild auf der Startseite angegeben und ich habe auch keinen Titel im Bild gefunden. Das Bild bekommt also keinen Titel.</p>';
          } else {
            echo '<p>❗️❓ Im Expertenmodus wird der Titel aus dem Bild gelesen. Ich habe allerdings keinen Titel im Bild gefunden. Das Bild hat also keinen Titel.</p>';
          }
          log_usage('2I', $user, 'No picture titel found in picture itself. Picture is without title.');
        }
      }
    

      //####################################################################
      // generate requestet EXIF values

      // values might start with a special character wich have the folowwing rules:
      // . : value is only displayed
      // = : value is calculated (and displayed)
      // ? : only the existance of this value is displayed as $tag_is_set or $tag_not_set
      // any other character : value will be set using the exiftool

      $requested['.FileName']              = '';

      $requested['Title']                  = $description;                      // XMP
      $requested['ObjectName']             = $requested['Title'];               // IPTC

      if($description_isset) {
        $requested['ImageDescription']     = $user . ' / ' . $description;      // EXIF (bot)
      } else {
        $requested['ImageDescription']     = $user;                             // EXIF (bot)
      }
      $requested['Description']            = $requested['ImageDescription'];    // XMP
      $requested['Caption-Abstract']       = $requested['ImageDescription'];    // IPTC

      $requested['.ImageWidth']            = '';                                // FILE
      $requested['.ImageHeight']           = '';                                // FILE
      $requested['=LongestSide']           = '2000-2048';                       
      // $requested['.ImageWidth']            = '2000';                            // FILE
      // $requested['.ImageHeight']           = '2000';                            // FILE
      // $requested['.ExifImageWidth']        = $requested['.ImageWidth'];      // 
      // $requested['.ExifImageHeight']       = $requested['.ImageHeight'];
      $requested['.Orientation']           = '';

      $requested['Artist']                 = $creator;                          // EXIF
      $requested['Creator']                = $requested['Artist'];              // XMP
      $requested['By-line']                = $requested['Artist'];              // IPTC

      $requested['Copyright']              = $license;                          // EXIF
      $requested['Rights']                 = $requested['Copyright'];           // XMP
      $requested['CopyrightNotice']        = $requested['Copyright'];           // IPTC
      // $requested['ProfileCopyright']       = ''; // not user specific

      $requested['.URL']                   = '';
      $requested['.WebStatement']          = $requested['.URL'];
      $requested['.CreatorWorkURL']        = $requested['.URL'];

      // $requested['?GPS']                   = $no_geo ? $tag_not_set : $tag_is_set;
      $requested['=GPS']                   = $no_geo ? $tag_not_set : $tag_is_set;
      // $requested['.GPS']                   = ''; // debug
      $requested['.GPSPosition']           = '';

      $requested['.CreateDate']            = '';                                // EXIF
      if($requested_period_type == 'M') {
        $requested['=Month']               = $requested_month;
      } else {
        $requested['=Week']                = $requested_week;
      }


      //####################################################################
      // processing

      echo '<h2>Bearbeiten deines Bildes ...</h2>' . PHP_EOL;


      //--------------------------------------------------------------------
      // show all exif data if requested

      $exif_html = get_exif_data($new_path, TRUE);
      echo '<div id="drawer1">' . PHP_EOL;
      echo 'Dein Bild hat ' . (count($exif_html) - 4) . ' Metadaten.<br />' . PHP_EOL;
      echo 'Wenn du diese Daten sehen möchtest, kannst du dir eine Tabelle mit allen Metadaten deines hochgeladenen Bildes hier anzeigen lassen. <br />' . PHP_EOL;
      echo 'Tabelle <a class="show" href="#drawer1">anzeigen</a><a class="hide" href="#">verstecken</a>.' . PHP_EOL;
      echo PHP_EOL . '<p><div class="hideable"><table style="border:1">' . PHP_EOL;
      $j = 0;
      foreach($exif_html as $hline) {
        if( $j > 2) {
          echo $hline . PHP_EOL;
        }
        $j += 1;
      }
      echo '<br /></div></div>' . PHP_EOL;


      //--------------------------------------------------------------------
      // resize picture

      $longest_side = get_longest_side($exif_data_orig);
      if(($longest_side >= 2000) and ($longest_side <= 2048)) {
        echo '<p>✅ Dein Bild hat schon die passende Größe. Es erfolgt keine Anpassung.</p>' . PHP_EOL;
      } else {
        $command =  $convert_command . ' ' . escapeshellarg($new_path) .
                    ' -resize 2000x2000 ' . escapeshellarg($tmp_file) .
                    ' 2>&1';
        exec($command, $data, $result);
        if($debugging) { // debug
          echo "<p>command: "; print_r($command);
          echo "<br>data: <br><pre>"; print_r($data); echo "</pre>";
          echo "<br>result: "; print_r($result);
          echo "</p>";
        }
        if($result !== 0) {
          log_command_result($command, $result, $data, $user);
          cancel_processing('Fehler bei der Größenänderung.');
        }
        if(unlink($new_path) == false) {
          cancel_processing('Fehler beim Löschen der alten Datei. (resize)');
        }
        if(rename($tmp_file, $new_path) == false) {
          cancel_processing('Fehler beim Umbennen der temporärern Datei. (resize)');
        }
        echo '<p>✅ Dein Bild wurde auf die passende Größe von 2000 Pixeln für die längste Seite angepasst.</p>' . PHP_EOL;
      }

      //--------------------------------------------------------------------
      // update picture EXIF to requested/required attributes

      if($expertmode) {
        echo '<p>Oh! 🧐 Expertenmodus. Es werden keine Metadaten geändert.</p>' . PHP_EOL;
      } else {

        // build exiftool commandline parameters
        $et_param = ' ';
        foreach($requested as $tag=>$tag_value) {
          if($debugging and false) { echo "<p>TAG:$tag:VALUE:$tag_value:</p>"; }
          if((substr($tag,0,1) == '.') or (substr($tag,0,1) == '?') or (substr($tag,0,1) == '=') ) { continue; }
          if(strlen($tag_value) == 0) { continue; }
          $et_param = $et_param . ' -' . $tag . '=' . escapeshellarg($tag_value);
        }
        // remove GEO data
        if($no_geo) {
          $et_param = $et_param . ' -gps:all= -xmp:geotag= ';
        }
        // run command
        if(strlen(trim($et_param))==0) {
          echo '<p>Keine Metadaten-Anpassung notwendig.<p>';
        } else {
          // exiftool -s = very short output of tag names
          //          -v = verbose output
          $command =  $exiftool_command . ' -v2 -s -overwrite_original ' . $et_param .
                      ' ' . escapeshellarg($new_path) . ' 2>&1';
          exec($command, $data, $result);
          if($debugging) { // debug
            echo "<p>command: "; print_r($command);
            echo "<br>data: <br><pre>"; print_r($data); echo "</pre>";
            echo "<br>result: "; print_r($result);
            echo "</p>";
          }
          if($result !== 0) {
            log_command_result($command, $result, $data, $user);
            echo '<p>⚠️ Problem bei der Änderung der Metadataten aufgetreten.</p>';
          }
          echo '<p>✅ Die Metadaten in deinem Bild wurden angepasst.</p>' . PHP_EOL;
        }

      }

      
      //####################################################################
      // display picture attributes (EXIF) existing compared to requested

      echo '<h2>Eckdaten des <i>überarbeiteten</i> Bildes</h2>';
      $exif_data = get_exif_data($new_path);
      $all_good = exif_display($exif_data, $requested, $exif_data_orig, TRUE);

      //--------------------------------------------------------------------
      // check picture date

      $date_info = get_any_picture_date_info($exif_data_orig);
      if( $date_info['prio'] == 99 ) {
        echo '<p>🛑 Achtung, in deinem Bild habe ich keine Datumsangaben gefunden.</p>';
        $all_good = false;
      }

      

      //####################################################################
      // display picture  and  furhter actions (buttons) to delete (and upload) picture

      echo '<h2>Das überarbeitete Bild! </h2>';
      echo '<p><img src="' . $new_path . '" alt="Your processed WeeklyPic" width="600" ><br />';
      echo '<small>Falls dein Bild gedreht dargestellt wird, berücksichtigt dein Browser den Style "image-orientation: from-image;" nicht. (Firefox kann das.) Das ist allerdings kein Problem für WeeklyPic.</small></p>';
      // HINT: Image Orientation (find it in the css style above) is currently only supported by Firefox
      // IDEA: Rotate a portrait image?

      $_SESSION['pathfilename'] = $new_path;
      $_SESSION['filebasename'] = $filename;
      $_SESSION['user']         = $user;
      $_SESSION['filename']     = $filename.'.'.$extension;    
      $_SESSION['per_type']     = $requested_period_type;
      $_SESSION['period']       = $requested_period;
      if($requested_period_type == 'W') {
        $_SESSION['year']         = get_picture_year_of_week($exif_data);
      } else { // Month
        $_SESSION['year']         = get_picture_year($exif_data);
      }

    echo '<h2>Und nun?</h2>';

    if($all_good == false) {
      echo '<p><em>⚠️ Es scheint ein Problem mit deinem Bild zu geben. Schaue mal im Abschnitt "Eckdaten des überarbeiteten Bildes".';
      echo '<br />Dort markiert ein 🛑 das Problem.';
      echo ' Bitte prüfe das und probiere es noch mal.</em></p>';
      if($pushing_pic > 0) {
        echo '<p>Solltest du meinen, dass alles in Ordnung ist, kannst du das Bild dennoch für Weeklypic bereitstellen. ';
        echo '<em></em>Die Admins prüfen das dann und müssen das Bild manuell in die Galerie verschieben.</em></p>'; 
        echo '<p><form method="post" action="final.php?' . htmlspecialchars(SID) . '">';
        echo '<input type="submit" name="upload2" value="für WeeklyPic zum prüfen bereitstellen und löschen">&nbsp;&nbsp;&nbsp;'; 
        echo '<input type="submit" name="delete" value="jetzt löschen" >&nbsp;&nbsp;&nbsp;';
        echo '</form></p>';
      }
    } else {

    ?>

    <p><?php if($pushing_pic > 0) { echo 'Hier kannst du das Bild nun direkt für WeeklyPic bereitstellen und hier löschen.<br>'; } ?>
       Sollte dir das Ergebnis hier nicht gefallen, solltest du das Bild hier löschen. (Sonst wird es auch irgendwann später gelöscht.)</p>
    <p><form method="post" action="final.php?<?php echo htmlspecialchars(SID); ?>">
      <?php if($pushing_pic > 0) { echo '<input type="submit" name="upload" value="für WeeklyPic bereitstellen und löschen">&nbsp;&nbsp;&nbsp;'; } ?>
      <input type="submit" name="delete" value="jetzt löschen" >&nbsp;&nbsp;&nbsp;
    </form></p>

    <?PHP } ?>

    <p>Du kannst das bearbeitete Bild (mit einem Rechtsklick auf das Bild)
       für dich herunterladen.</p>

  </body>
</html>
