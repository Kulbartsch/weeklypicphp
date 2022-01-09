<?PHP 

    // check and create a folder
    function create_folder($destination) {
      global $user;
      log_debug('Checking directory.', $destination);
      if( !is_dir($destination) ) {
        if( is_file($destination) ) {
          log_usage('3E', $user, $destination . " is a file, not a directory.");
          echo '<p>⚡️ Fehler beim Verschieben der Bild-Datei. ' . $destination . ' ist eine Datei.</p>';
          echo '<p>Das sollte nicht passieren. Bitte informiere einen Admin über das Problem.</p>';
          return FALSE;
        } else {
          if( !mkdir($destination) ) {
            log_usage('3E', $user, $destination . " is a file, not a directory.");
            echo '<p>⚡️ Fehler beim Verschieben der Bild-Datei. Verzeichnis ' . $destination . ' kann nicht angelegt werden.</p>';
            echo '<p>Das sollte nicht passieren. Bitte informiere einen Admin über das Problem.</p>';
            return FALSE;  
          } else {
            log_debug('Created directory.', $destination);
          }
          clearstatcache();
          return TRUE;
        }
      }
      log_debug( 'OK, directory exists.', $destination);
      return TRUE;
    }

    // store folder in directory hirarcchy, add sub-folder if necessary. create file with metainformation as well 
    // Structure is: $destination/[year]/[period type]/[period m or w]/[w|m]_[period]_[username].jpg
      function store_file($pathfilename, $destination_folder, $year, $per_type, $period, $filebasename, $comment, $user, $description, $error) {

        $destination = $destination_folder . '/' . $year;
        clearstatcache();
        if( !create_folder($destination) ) { return false; }
        $destination = $destination . '/' . $per_type;
        if( !create_folder($destination) ) { return false; }
        $destination = $destination . '/' . $period;
        if( !create_folder($destination) ) { return false; }
        $destination = $destination . '/';

        if( is_file($destination . $filebasename . '.jpg') ) { // deleting existing files
          if( !unlink($destination . $filebasename . '.jpg') ) {
            log_usage('I3', $user, 'Error deleting existing file ' . $destination . $filebasename . '.jpg');
          } else {
            echo "<p>Bereits vorhandenes Bild wird gelöscht.</p>";
            log_debug('OK, removed existing file', $destination . $filebasename . '.jpg');
          }
          unlink($destination . $filebasename . '.meta');
          unlink($destination . $filebasename . '.txt');
        } 

        log_debug('Moving file', $pathfilename . ' to ' . $destination);
        if( !move_file($pathfilename, $destination) ) {
          log_debug('Error moving file', $pathfilename . '->' . $destination);
          return false;
        }

        if( strlen(trim($comment)) <> 0  || strlen(trim($error)) <> 0 ) {
          file_put_contents($destination . "/" . $filebasename . ".txt", $comment . PHP_EOL . '[' . $error . ']' . PHP_EOL);
        }

        $metadata = array( '@year:' . $year . PHP_EOL,
                           '@per_type:' . $per_type . PHP_EOL,
                           '@per_text:' . ( ( $per_type == 'm' ) ? 'Monat' : 'Woche' ) . PHP_EOL,
                           '@period:' . $period . PHP_EOL,
                           '@user:' . $user . PHP_EOL,
                           '@description:' . $description . PHP_EOL,
                           '@filename:' . $filebasename . '.jpg' . PHP_EOL,
                           '@error:' . $error . PHP_EOL,
                           '@comment:' . $comment . PHP_EOL,
                           '+one_picture.in' . PHP_EOL );
        file_put_contents($destination . "/" . $filebasename . ".meta", $metadata);
        
        return TRUE;
      }

      function find_files_to_check() {
        $files_to_check = array();
        exec('find ../images -name  "[wm]_*.txt"', $lines, $result);
        echo '<p>FEHELR! find images gab einen Fehler zurück.</p>';
        foreach ($lines as $line) { $files_to_check[] = $line; }
        return $files_to_check;
      }

      function number_of_files_to_check() {
        return count(find_files_to_check());
      }

      // function moves a set of files for one picture to nanother period
      // $pathfilname - .jpg picture name viewed from admin view like ../images/2022/W/01/w_01_alexander.xxx
      // $year - move to year
      // $per_type - 'M' for month or 'W' for week
      // $period - 1..12 for Month or 1..52 for week
      function move_picture_set($pathfilename, $year, $per_type, $period) {
        $destination = '../images/' . $year . '/' . $per_type . '/' . $period;
        create_folder($destination);
        $dir = new DirectoryIterator(substr($pathfilename, 0, -3) . '*');

        foreach ($dir as $fileinfo) {
            $fn = $fileinfo->getFileName();
            if( !move_file($fn, $destination) ) {
                log_debug('Error moving file', $pathfilename . '->' . $destination);
                //return false;
            }
            //return TRUE;
        }
        clearstatcache();
      }

?>