<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>.:fotoaufbrecherli:.</title>
    <style type="text/css">
      body {margin:5% auto; line-height:1.6; font-size:18px;
        color:#444; padding:0 10px;
        background:#eee;
        -webkit-font-smoothing:antialiased;
        font-family:"Helvetica Neue",Helvetica,Arial,Verdana,sans-serif }
      h1,h2,h3 {line-height:1.2}
      table{ border-collapse: collapse; }
      th, td { padding: 3px;  text-align: left; }
      table, th, td { border: 1px solid black; }
    </style>
  </head>
  <body>

    <?php

      $debugging = true;
      $convert_command = '/usr/local/bin/convert';  // imagemagick covert

      include 'src/exif_parsing.php';

      // TODO: replace "die" with function and flag to keep HTML result clean and offer a link back.
      // IDEA: generate filename from parameters (week/month, w/m-number, Weekly-Pic-Name)
      // IDEA: validate picture date against parameters week/month and w/m-number

      //####################################################################
      // _POST Var Handling

      // TODO: Harden _POST values (Restrict to certain characters)

      $user = $_POST["user"];
      $creator = $_POST["creator"];
      $license = $_POST["license"];
      $description = $_POST["description"];
      $description_isset = false;
      echo "<h1>Hallo! ❤️</h1>";
      echo "<p>Grüezi $user.</p>";
      if (!empty($description))
      {
        echo "<p>Dein Bild soll also <i>$description</i> heissen?</p>";
        $description_isset = true;
      }

      //####################################################################
      // File Upload Handling
      $upload_folder = '_files/'; //Das Upload-Verzeichnis
      $fileToUpload  = $_FILES["fileToUpload"];
      $file_basename = basename($fileToUpload["name"]);
      $upload_file   = $upload_folder . $file_basename;
      $filename      = pathinfo($fileToUpload['name'], PATHINFO_FILENAME);
      if($debugging == true) {
        echo "<p>file name: " .      $filename ;
        echo "<br>file type: " .     $fileToUpload["type"];
        echo "<br>file tmp name: " . $fileToUpload["tmp_name"];
        echo "<br>file basename:     $file_basename";
        echo "<br>upload filename:   $upload_file</p>";
      }

      //Überprüfung der Dateiendung
      $extension = strtolower(pathinfo($upload_file, PATHINFO_EXTENSION));
      $allowed_extensions = array('png', 'jpg', 'jpeg', 'JPG', 'JPEG');
      if(!in_array($extension, $allowed_extensions)) {
        die("Ungültige Dateiendung.");
      }

      //Überprüfung der Dateigröße
      $max_size = 10000*1024; //10MB
      if($_FILES['fileToUpload']['size'] > $max_size) {
        die("Bitte keine Dateien größer 10 MB hochladen");
      }

      //Überprüfung dass das Bild keine Fehler enthält
      if(function_exists('exif_imagetype')) { //Die exif_imagetype-Funktion erfordert die exif-Erweiterung auf dem Server
        $allowed_types = array(IMAGETYPE_PNG, IMAGETYPE_JPEG, IMAGETYPE_GIF);
        $detected_type = exif_imagetype($fileToUpload["tmp_name"]);
        if($debugging == true) {
          echo "<p>allowed extensions: ";
            foreach ($allowed_types as $x) echo $x . ", ";
          echo "<br>detected type: " . $detected_type . "</p>";
        }
        if(!in_array($detected_type, $allowed_types)) {
          die("Nur der Upload von Bilddateien ist gestattet. Du verwendest $detected_type .");
        }
      }
      else {
        die("No PHP-EXIF functions");
      }

      // Pfad zum Upload
      // IDEA: Oder Name generiertem Dateinamen erstellen?
      $new_path = $upload_folder.$filename.'.'.$extension;

      //Neuer Dateiname falls die Datei bereits existiert
      // IDEA: Oder bestehendes Bild überschreiben?
      if(file_exists($new_path)) { //Falls Datei existiert, hänge eine Zahl an den Dateinamen
        $id = 1;
        do {
          $new_path = $upload_folder.$filename.'_'.$id.'.'.$extension;
          $tmp_file = $upload_folder.$filename.'_'.$id.'_tmp.'.$extension;
          $id++;
        } while(file_exists($new_path));
      }

      //Alles okay, verschiebe Datei an neuen Pfad
      move_uploaded_file($fileToUpload['tmp_name'], $new_path);
      echo 'Bild erfolgreich hochgeladen: <a href="'.$new_path.'">'.$new_path.'</a>';

      //####################################################################
      // generate requestet EXIF values

      $requested['.FileName']              = '';

      $requested['Title']                  = $description;
      $requested['ObjectName']             = $requested['Title'];

      $requested['ImageDescription']       = $user . ' / ' . $description;
      $requested['Description']            = $requested['ImageDescription'];
      $requested['Caption-Abstract']       = $requested['ImageDescription'];

      $requested['.ImageWidth']            = '2000';
      $requested['.ImageHeight']           = '2000';
      $requested['ExifImageWidth']         = $requested['ImageWidth'];
      $requested['ExifImageHeight']        = $requested['ImageHeight'];

      $requested['Artist']                 = $creator;
      $requested['Creator']                = $requested['Artist'];
      $requested['By-line']                = $requested['Artist'];

      $requested['Copyright']              = $license;
      $requested['Rights']                 = $requested['Copyright'];
      $requested['CopyrightNotice']        = $requested['Copyright'];
      // $requested['ProfileCopyright']       = ''; // not user specific

      $requested['URL']                    = '';
      $requested['WebStatement']           = $requested['URL'];
      $requested['CreatorWorkURL']         = $requested['URL'];

      $requested['?GPS']                   = 'Nein';

      //####################################################################
      // display picture attributes (EXIF) existing compared to requested

      echo '<h2>Eckdaten des <i>hochgeladenen</i> Bildes</h2>';
      exif_display($new_path, $requested);

      //####################################################################
      // resize picture

      $command =  $convert_command . ' ' . escapeshellarg($new_path) .
                  ' -resize 2000x2000 ' . escapeshellarg($tmp_file);
      exec($command, $data, $result);
      if($debugging) { // debug
        echo "<p>command: "; print_r($command);
        echo "<br>data: <br><pre>"; print_r($data); echo "</pre>";
        echo "<br>result: "; print_r($result);
        echo "</p>";
      }
      if($result !== 0) {
        die('Fehler bei der Größenänderung.');
      }
      if(unlink($new_path) == false) {
        die('Fehler beim Löschen der alten Datei. (resize)');
      }
      if(rename($tmp_file, $new_path) == false) {
        die('Fehler beim Umbennen der temporärern Datei. (resize)');
      }

      //####################################################################
      // update picture EXIF to requested/required attributes

      // TODO: implement retagging

      //####################################################################
      // display picture attributes (EXIF) existing compared to requested

      echo '<h2>Eckdaten des <i>überarbeiteten</i> Bildes</h2>';
      exif_display($new_path, $requested);

      //####################################################################
      // display picture  and  furhter actions (buttons) to delete picture
      // IDEA: maybe directly send them to tim peters owncloud?

      echo '<h2>Das überarbeitete Bild! </h2>';
      echo '<p><img src="' . $new_path . '" alt="Your processed WeeklyPic" width="600"></p> ';

      // TODO: implement deletion

    ?>

    <h2>Und nun?</h2>
    <p>Nachdem du das Bild (mit einem Rechtsklick auf das Bild)
       heruntergeladen hast, lädst du es wieder auf
       <a href='https://upload.weeklypic.de/'>https://upload.weeklypic.de/</a> hoch. </p>
    <p><a href="index.php">Hier geht es dann an den Anfang zurück.</a></p>

  </body>
</html>
