<?PHP
  // configuration constants
  include 'src/config.php';

  include 'src/functions.php';
  include 'src/exif_parsing.php';

  // session (must be handled before any html code)
  session_start();

  // _POST Var Handling
  // IDEA: Restrict $user to  characters? [a-z,A-Z,_,0-9]
  $user         = sanitize_input("user", TRUE);
  $creator      = sanitize_input("creator", FALSE);
  $license      = sanitize_input("license", FALSE);
  $description  = sanitize_input("description", FALSE);
  $description_isset = false;
  if(array_key_exists("nogeo", $_POST)){
    $no_geo = true;
    $nogeo_cookie = 'checked';
  } else {
    $no_geo = false;
    $nogeo_cookie = ' ';
  }

  // cookie (must be handled before any html code)
  // Store common values cookies for next time, if requested
  if(array_key_exists("usecookie", $_POST)) {
    $cookie_value = implode( $cookie_split, array($user, $creator, $license, $nogeo_cookie) );
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
    </style>
  </head>
  <body>

    <?php

      // CHECK: Umlaute in user name chrashes exiftool, because umlaute are dropped. Might be fixed with Umlaut in title bug - check
      // TODO: Don't allow Umlaute and special characters in User name
      // BUG: Empty title results in wrong description (Take avaible title from existing exif data)
      // REVIEW: make output nicer if upload server config file is missing.
      // BUG: a not processed upload - i.e. picture is to big - is not detected = no filename
      // IDEA: make a web-page to show all EXIF data
      // IDEA: check for umlaute in requested picture title
      // IDEA: validate if picture is for the *current* week/month (and year) - warn if not
      // TODO: make all picture delivery services optional

      //####################################################################

      echo "<h1>Hallo! ❤️</h1>";
      echo "<p>Grüezi $user.</p>";

      if (!empty($description))
      {
        echo "<p>Dein Bild soll also <i>$description</i> heissen?!</p>";
        $description_isset = true;
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

      log_usage('2', $user);
      if(empty($user)) {
        cancel_processing("Fehler! Kein Weekly-Pic-Benutzernamen angegeben.");
      }

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
      // generate requestet EXIF values

      // values might start with a special character wich have the folowwing rules:
      // . : value is only displayed
      // = : value is calculated (and displayed)
      // ? : only the existance of this value is displayed as $tag_is_set or $tag_not_set
      // any other character : value will be set using the exiftool

      $requested['.FileName']              = '';

      $requested['Title']                  = $description;
      $requested['ObjectName']             = $requested['Title'];

      $requested['ImageDescription']       = $user . ' / ' . $description;
      $requested['Description']            = $requested['ImageDescription'];
      $requested['Caption-Abstract']       = $requested['ImageDescription'];

      $requested['.ImageWidth']            = '2000';
      $requested['.ImageHeight']           = '2000';
      // $requested['.ExifImageWidth']        = $requested['.ImageWidth'];
      // $requested['.ExifImageHeight']       = $requested['.ImageHeight'];
      $requested['.Orientation']           = '';

      $requested['Artist']                 = $creator;
      $requested['Creator']                = $requested['Artist'];
      $requested['By-line']                = $requested['Artist'];

      $requested['Copyright']              = $license;
      $requested['Rights']                 = $requested['Copyright'];
      $requested['CopyrightNotice']        = $requested['Copyright'];
      // $requested['ProfileCopyright']       = ''; // not user specific

      $requested['.URL']                   = '';
      $requested['.WebStatement']          = $requested['.URL'];
      $requested['.CreatorWorkURL']        = $requested['.URL'];

      $requested['?GPS']                   = $no_geo ? $tag_not_set : $tag_is_set;
      $requested['=GPS']                   = $no_geo ? $tag_not_set : $tag_is_set;
      $requested['.GPS']                   = ''; // debug
      $requested['.GPSPosition']           = '';

      $requested['.CreateDate']            = '';
      if($requested_period_type == 'M') {
        $requested['=Month']               = $requested_month;
      } else {
        $requested['=Week']                = $requested_week;
      }

      //####################################################################
      // display picture attributes (EXIF) existing compared to requested

      echo '<h2>Eckdaten des <i>hochgeladenen</i> Bildes</h2>';
      $exif_data = get_exif_data($new_path);
      exif_display($exif_data, $requested, FALSE);


      //####################################################################
      // processing

      //--------------------------------------------------------------------
      // resize picture

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


      //--------------------------------------------------------------------
      // update picture EXIF to requested/required attributes

      // build exiftool commandline parameters
      $et_param = ' ';
      foreach($requested as $tag=>$tag_value) {
        if($debugging and false) { echo "<p>TAG:$tag:VALUE:$tag_value:</p>"; }
        if( (substr($tag,0,1) == '.') or (substr($tag,0,1) == '?') or (substr($tag,0,1) == '=') ) { continue; }
        if( strlen($tag_value) == 0 ) { continue; }
        $et_param = $et_param . ' -' . $tag . '=' . escapeshellarg($tag_value);
      }
      // remove GEO data
      if($no_geo) {
        $et_param = $et_param . ' -gps:all= -xmp:geotag= ';
      }
      // run command
      if(strlen($et_param)==0) {
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
      }


      //####################################################################
      // display picture attributes (EXIF) existing compared to requested

      echo '<h2>Eckdaten des <i>überarbeiteten</i> Bildes</h2>';
      $exif_data = get_exif_data($new_path);
      $all_good = exif_display($exif_data, $requested, TRUE);


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
      echo '<p>⚠️ Es scheint ein Problem mit deinem Bild zu geben. (siehe "Eckdaten des überarbeiteten Bildes") ';
      echo '   Bitte prüfe das und probiere es noch mal. ';
      echo '   Solltest du meinen, dass alles in Ordnung ist, melde dich im Slack im #entwickler_talk.</p>'; 
      cancel_processing('Die Bilddaten sind nicht in Ordnung.');
    }

    // TODO: inform user about the ways the picture is pushed 
    ?>

    <p><?php if($pushing_pic > 0) { echo 'Hier kannst du das Bild nun dirket für WeeklyPic bereitstellen und hier löschen.<br>'; } ?>
       Sollte dir das Ergebnis hier nicht gefallen, solltest du das Bild hier löschen. (Sonst wird es auch irgendwann später gelöscht.)</p>
    <p><form method="post" action="final.php?<?php echo htmlspecialchars(SID); ?>">
      <?php if($pushing_pic > 0) { echo '<input type="submit" name="upload" value="für WeeklyPic bereitstellen und löschen">&nbsp;&nbsp;&nbsp;'; } ?>
      <input type="submit" name="delete" value="jetzt löschen" >&nbsp;&nbsp;&nbsp;
    </form></p>
    <p>Du kannst das bearbeitete Bild (mit einem Rechtsklick auf das Bild)
       für dich herunterladen heruntergeladen.</p>

  </body>
</html>
