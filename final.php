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

      log_debug('>>>> START final.php','');

      // get values from Session
      $pathfilename = $_SESSION['pathfilename'];
      $filebasename = $_SESSION['filebasename'];
      $user         = $_SESSION['user'];
      $filename     = $_SESSION['filename'];
      $per_type     = $_SESSION['per_type'];
      $period       = $_SESSION['period'];
      $year         = $_SESSION['year'];

      // upload
      if (isset($_POST['upload'])) { // upload button was klicked
        log_usage('3u', $user);

        if($pushing_pic == 0) {
          echo '<p>⚡️ Fehler: WeeklyPic Bereitstellung angefordert, aber keine Ziel Konfiguration gefunden.</p>';
        }
        
        if(($pushing_pic & $push_cloud) > 0) {
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
            echo '<p>⚡️ Problem beim Cloud-Upload aufgetreten.</p>';
          } else {
            echo '<p>✅ Das Bild wurde hochgeladen! 😃</p>';
          }
        } 

        if(($pushing_pic & $push_ftp) > 0) {
          
          // Use configured command for upload
          $upload_dir = uploadWPdir($per_type, $period, $year);
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
            log_usage('3E', $user, 'FTP Upload error. ' . $upload_dir . ' ' . $filename);
          } else {
            echo '<p>✅ Das Bild wurde hochgeladen! 😃</p>';
            log_debug('Upload OK','');
            log_usage('3F', $user, 'FTP Upload to ' . $upload_dir . ' ' . $filename);
          }  
        } 

        if(($pushing_pic & $push_filesystem) > 0) {
          move_file($pathfilename, $destination_folder); 
        }

      } elseif(isset($_POST['upload2'])) { // upload button to check directory
        // TODO: ... 
      } else {  // no upload 
        log_usage('3d', $user);
      }

      // delete - always, except is moved by filesystem delivery
      if(($pushing_pic & $push_filesystem) == 0) {
        delete_file($pathfilename);
      }

    ?>

    <p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im WeeklyPic-Slack-Channel #entwickler-talk.</p>
    <p>Gehe an den <a href="index.php">Anfang</a> zurück um ein weiteres Bild zu bearbeiten.</p>
    <p>Oder vielleicht möchtest du dich auf <a href="https://www.weeklypic.de/">Weeklypic</a> umschauen.</p>

  </body>
</html>
