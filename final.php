<?PHP session_start(); ?>
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

  	<h1>Fertig! 😀</h1>

    <?php
      // configuration constants
      include 'src/config.php';
      // more functions
      include 'src/functions.php';
      include 'src/slack.php';
      include 'src/filestore.php';

      log_debug('>>>> START final.php','');

      // get values from Session
      $pathfilename = $_SESSION['pathfilename'];
      $filebasename = $_SESSION['filebasename'];
      $user         = $_SESSION['user'];
      $filename     = $_SESSION['filename'];
      $per_type     = $_SESSION['per_type'];
      $period       = $_SESSION['period'];
      $year         = $_SESSION['year'];
      $description  = $_SESSION['description'];
      $error        = $_SESSION['error'];
      $comment      = $_POST['comment'];

      log_usage('3I', $user, '', FALSE, TRUE);
      log_debug('Final Pathfilename', $pathfilename);

      // check if file exists
      if( ! file_exists($pathfilename) ) {
          echo '<p>Dein Bild ist nicht mehr da. Hast du diese Web-Seite vielleicht neu geladen? '
          echo 'Oder bist hier hin zurück navigiert? Dann kann das passieren, aber das ist dann kein Problem. ';
          echo 'Ob dein Bild erfolgreich verarbeitet wurde, konntest du der Seite vor dem neuen laden entnehmen.</p>';
          log_usage('3E', $user, 'File missing');
          cancel_processing('Datei fehlt.');
      }

      // upload
      if (isset($_POST['upload']) or isset($_POST['upload2'])) { // upload button was klicked

        if($pushing_pic == 0) {
          cancel_processing("⚡️ Fehler: WeeklyPic Bereitstellung angefordert, aber keine Ziel Konfiguration gefunden.");
          // echo '<p>⚡️ Fehler: WeeklyPic Bereitstellung angefordert, aber keine Ziel Konfiguration gefunden.</p>';
        }
        
        if((($pushing_pic & $push_cloud) > 0) and isset($_POST['upload'])) {
          $command = $curl_command . ' -u ' . $upload_login . ' -X PUT --data-binary @"' .
                    $pathfilename . '" "' . $upload_server . $filebasename . '.jpg" 2>&1';
          exec($command, $data, $result);
          if($debugging) { // debug
            echo "<p>command: "; print_r($command);
            echo "<br>data: <br><pre>"; print_r($data); echo "</pre>";
            echo "<br>result: "; print_r($result);
            echo "</p>";
          }
          if($result !== 0) {
            log_command_result($command, $result, $data, $user);
            // echo '<p>⚡️ Problem beim Cloud-Upload aufgetreten.</p>';
            log_usage('3E', $user, 'Error uploading to Cloud');
            cancel_processing('⚡️ Problem beim Cloud-Upload aufgetreten.');

          } else {
            echo '<p>✅ Das Bild wurde hochgeladen! 😃</p>';
            log_usage('3I', $user, 'Uploaded to Cloud');
          }
        } 

        if((($pushing_pic & $push_ftp) > 0) and (isset($_POST['upload']) or isset($_POST['upload2']))) {
          
          // Use configured command for upload
          if(isset($_POST['upload2'])) {
            $upload_dir = $check_dir;
            log_usage('3I', $user, 'Requested upload to ' . $upload_dir . ' (upload2)');
            $message2 = 'System-Fehlermeldung: ' . $error;
            if( strlen($comment) > 0) {
              $message2 = ' Benutzer Kommentar: «' . $comment . '»  ' . $message2;
            }
            log_usage('3I', $user, $message2);
            slack('Hallo Admins! ' . $user . ' lädt das Bild ' . $filename . ' in den Prüfordner ' . $upload_dir . ' hoch. ' . $message2, '#weeklypic-adm' );
          } else {
            $upload_dir = uploadWPdir($per_type, $period, $year);
            log_usage('3I', $user, 'Requested upload to ' . $upload_dir . ' (upload)');
          }
          $command = str_replace('$fqfn$', $pathfilename, $ftp_exec);
          $command = str_replace('$file$', $filename, $command);
          $command = str_replace('$dir$', $upload_dir, $command);

          log_debug('Upload to', $upload_dir . ' ' . $filename);

          // Alternative Upload using curl on SFTP
          //     curl  -k "sftp://844.421.42.23:22/CurlPutTest/" --user "testuser:testpassword" -T "C:\test\testfile.xml" --ftp-create-dirs
          // $command = $curl_command . ' -k "' . $ftp_destination . '" --user "' . $ftp_login . '" -T "' .
          //           $pathfilename . '" --ftp-create-dirs 2>&1';

          exec($command, $data, $result);
          if($debugging) { // debug
            echo "<p>command: "; print_r($command);
            echo "<br>data: <br><pre>"; print_r($data); echo "</pre>";
            echo "<br>result: "; print_r($result);
            echo "</p>";
          }
          if($result !== 0) {
            log_command_result($command, $result, $data, $user);
            echo '<p>⚡️ Problem beim FTP-Upload aufgetreten.</p>';
            log_debug('Upload Error','');
            log_usage('3E', $user, 'FTP upload error. ' . $upload_dir . ' ' . $filename);
            cancel_processing('Problem beim FTP-Upload aufgetreten.');
          } else {
            echo '<p>✅ Das Bild wurde für WeeklyPic hochgeladen! 😃</p>';
            log_debug('Upload OK','');
            log_usage('3I', $user, 'FTP upload successful to ' . $upload_dir . ' ' . $filename);
          }  
          echo '<p>Dateiname: ' . htmlspecialchars($filename) . '<br />' . PHP_EOL;
          echo 'für Zeitraum: ' . $year;
          if($per_type == 'M') {
            echo ' Monat '; 
          } else {
            echo ' Woche ';
          }
          echo $period . '</p>' . PHP_EOL;
        } 

        if(($pushing_pic & $push_filesystem) > 0) {
          log_debug('Final calling', 'store file');
          if( store_file($pathfilename, $destination_folder, $year, $per_type, $period, $filebasename, $comment, $user, $description, $error) ) {
            // echo '<p>✅ Das Bild wurde für den WeeklyPic-Slack kopiert! 😃</p>';
            log_usage('3I', $user, 'File moved'); 
          } else {
            // echo '<p>⚡️ Problem beim kopieren in lokales Verzeichnis.</p>';
            log_usage('3E', $user, 'Error moving file'); 
            cancel_processing('Error moving file');
          }
        }

      } elseif(isset($_POST['delete'])) {  // no upload 
        log_usage('3I', $user, 'only deleting file');
      } else {
        log_usage('3E', $user, 'Unknown request');
      }

      // delete - always, except is moved by filesystem delivery
      if(($pushing_pic & $push_filesystem) == 0) {
        delete_file($pathfilename);
      }

    ?>

    <p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im WeeklyPic-Slack-Channel #entwickler-talk.</p>
    <p>Gehe an den <a href="index.php">Anfang</a> zurück um ein weiteres Bild zu bearbeiten.</p>
    <p>Oder vielleicht möchtest du dich auf <a href="https://www.weeklypic.de/">WeeklyPic</a> umschauen.</p>

  </body>
</html>
