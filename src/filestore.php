<?PHP 

  // $destination_folder <> 'na') {
  // $pushing_pic = $pushing_pic | $push_filesystem;

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
      function store_file($pathfilename, $destination_folder, $year, $per_type, $period, $filebasename, $comment, $user, $description) {

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
        };

        if( strlen(trim($comment)) === 0) {
          file_put_contents($destination . "/" . $filebasename . ".txt", $comment);
        }

        $metadata = array( '@year:' . $year . PHP_EOL,
                           '@per_type:' . $per_type . PHP_EOL,
                           '@per_text:' . ( ( $per_type == 'm' ) ? 'Monat' : 'Woche' ) . PHP_EOL,
                           '@period:' . $period . PHP_EOL,
                           '@user:' . $user . PHP_EOL,
                           '@description:' . $description . PHP_EOL,
                           '@filename:' . $filebasename . '.jpg' . PHP_EOL,
                           '@comment:' . $comment . PHP_EOL );
        file_put_contents($destination . "/" . $filebasename . ".meta", $metadata);
        
        return TRUE;
      }

?>
