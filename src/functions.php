<?php

  // Processing is stoped with "die", closing <body> and <hmtl> tags.
  function cancel_processing($msg) {
    echo "<p><strong>🛑 " . $msg . "</strong><br/>";
    echo "<em>Die Verarbeitung wird abgebrochen.</em></p>";
    echo '<p>Gehe an den <a href="index.php">Anfang</a> zurück um es noch einmal zu probieren.</p>';
    echo '</body></hmtl>';
    die();
  }


  function validate_number_and_return_string($n, $min, $max) {
    $num = intval($n);
    if($num < $min OR $num > $max) {
      cancel_processing("Fehler! Wert $n ist nicht im Bereich $min - $max !");
    }
    return sprintf('%02d', $num);
  }


  function sanitize_input($param, $required) {
    if (empty($_POST[$param])) {
      if($required){
        cancel_processing("Parameter '$param' wurde nicht angegeben.");
      } else {
        return '';
      }
    } else {
      return htmlspecialchars(trim($_POST[$param]));
    }
  }


  function delete_file($filename) {
    if(file_exists($filename)) {
      if(unlink($filename) == false) {
        echo '<p>⚡️ Fehler beim Löschen der Bild-Datei. Das sollte nicht passieren.</p>';
        echo '<p>Bitte informiere einen Admin über das Problem.</p>';
        return FALSE;
      } else {
        if(file_exists($filename)) {
          echo '<p>⚡️ Fehler beim Löschen der Bild-Datei. (2)</p>';
          echo '<p>Bitte informiere einen Admin über das Problem.</p>';
          return FALSE;
        } else {
          echo '<p>♻️ Das Bild wurde von diesem Server gelöscht.</p>';
          return TRUE;
        }
      }
    } else {
      echo '<p>⚡️ Die zu löschende Bild-Datei existiert nicht. 🤔</p>';
      echo '<p>Bitte informiere einen Admin über das Problem.</p>';
      return FALSE;
    }
  }


  function move_file($filename, $destination) {
    if(file_exists($filename)) {
      if(substr($destination,-1) == '/') { 
        $moveto = $destination . basename($filename); 
      } else {
        $moveto = $destination . '/' . basename($filename); 
      }
      if(file_exists($moveto)) {
        echo '<p>Ein Bild mit diesem Namen existiert schon WeeklyPic Eingangs-Verzeichnis. Das vorhandene Bild wird gelöscht und durch das neue ersetzt. </p>';
        delete_file($moveto);
      } 
      if(rename($filename, $moveto) == false) {
        echo '<p>⚡️ Fehler beim Verschieben der Bild-Datei (1). Das sollte nicht passieren. </p>';
        echo '<p>Bitte informiere einen Admin über das Problem.</p>';
        return FALSE;
      } else {
        if(file_exists($filename)) {
          echo '<p>⚠️ Fehler beim Verscheiben der Bild-Datei. (2) Das sollte nicht passieren. </p>';
          echo '<p>Bitte informiere einen Admin über das Problem.</p>';
          return FALSE;
        } else {
          echo '<p>✅ Das Bild wurde ins WeeklyPic Eingangs-Verzeichnis verschoben.</p>';
          return TRUE;
        }
      }
    } else {
      echo '<p>⚠️ Die Bild-Datei existiert nicht (mehr). 🤔 Das sollte nicht passieren. </p>';
      echo '<p>Bitte informiere einen Admin über das Problem.</p>';
      return FALSE;
    }
  }


  function log_command_result($cmd, $result, $output, $user) {
    global $command_log;
    $log_msg = PHP_EOL . 'time:' . date("c") . ';' . $user . PHP_EOL .
               'command: ' . $cmd . PHP_EOL . 'result: ' . $result . PHP_EOL .
               'output:' . PHP_EOL . print_r($output, TRUE) . PHP_EOL .
               '-- END --' . PHP_EOL . PHP_EOL ;
    if(file_put_contents($command_log, $log_msg, FILE_APPEND) === FALSE) {
      echo "<p>⚠️ Problem bei Command-Log schreiben</p>";
    }
    log_usage('CE', $user);
  }

  function log_usage($page, $user) {
    global $usage_log;
    global $usage_logging;
    if($usage_logging == 0) { return; }
    if($usage_logging == 1) {
      $log_msg = date("c") . ';' . $page . PHP_EOL ;
    } else {
      $log_msg = date("c") . ';' . $page . ';' . $user . PHP_EOL ;
    }
    if(file_put_contents($usage_log, $log_msg, FILE_APPEND) === FALSE) {
      echo "<p>⚠️ Problem bei Log schreiben</p>";
    }
  }

  function get_picture_date($tags) {
    $exif_create_date = exif_get_tag_value($tags, 'CreateDate');
    if( $exif_create_date === '' ) {
      return 'nodate';
    } else {
      return DateTime::createFromFormat('Y:m:d G:i:s', $exif_create_date);
    }
  }

  // weekly pic picture week is shifted by 2 days in the future 
  function get_picture_wp_week($tags) {
    $picdate = get_picture_date($tags);
    if($picdate == 'nodate') {
      return 0;
    } else {
      return $picdate->add(new DateInterval('P2D'))->format('W');
    }
  }

  // get year of picture 
  function get_picture_year($tags) {
    $picdate = get_picture_date($tags);
    if($picdate == 'nodate') {
      return 0;
    } else {
      return $picdate->format('Y');
    }
  }
    
  // get year of week from last day of week
  function get_picture_year_of_week($tags) {
    $picdates = picture_dates($tags);
    if( $picdates['result'] != 'ok') {
      return 0;
    } else {
      return $picdates['wp_week_end_date']->format('Y');
    }
  }


  // calculate several date parts - currently not used
  function picture_dates($tags) {
    $returns['result'] = 'ok';
    // get CreateDate tag
    $exif_create_date = exif_get_tag_value($tags, 'CreateDate');
    if( $exif_create_date === '' ) {
      $returns['result'] = 'Error: Picture has no create date!';
      return $returns;
    }
    // convert Tag to date (CreateDate : 2019:05:15 22:58:54)
    $returns['date']  = DateTime::createFromFormat('Y:m:d G:i:s', $exif_create_date);
    // $returns['month'] = $returns['date']->format('m'); 
    // $returns['week']  = $returns['date']->format('W'); 
    // $returns['year']  = $returns['date']->format('Y'); 
    $dayinweek = $returns['date']->format('w');  // Sunday = 0 ... Saturday = 6
    if ($dayinweek < 6) {
      $returns['wp_week_start_date'] = $returns['date']->sub(new DateInterval('P'.($dayinweek+1).'D'));
      $returns['wp_week_end_date']   = $returns['date']->add(new DateInterval('P'.(5-$dayinweek).'D'));
    } else { // = 6 = saturday
      $returns['wp_week_start_date'] = $returns['date']; 
      $returns['wp_week_end_date']   = $returns['date']->add(new DateInterval('P6D'));
    }
    // $returns['wp_year_start_date'] = 
    // $returns['wp_year_end_date']   = 
    $returns['wp_week']            = $returns['wp_week_end_date']->format('W');
  }


  function uploadWPdir($per_type, $period, $year) { // returns path
    if( $per_type == 'w' OR $per_type == 'W') {
      return $year . '-woche-' . $period;
    } else {  // assuming $per_type == 'm' -> month 
      switch ($period) {
        case 1:
          return 'januar-' . $year;
          break;
        case 2:
          return 'februar-' . $year;
          break;
        case 3:
          return 'maerz-' . $year;
          break;
        case 4:
          return 'april-' . $year;
          break;
        case 5:
          return 'mai-' . $year;
          break;
        case 6:
          return 'juni-' . $year;
          break;
        case 7:
          return 'juli-' . $year;
          break;
        case 8:
          return 'august-' . $year;
          break;
        case 9:
          return 'september-' . $year;
          break;
        case 10:
          return 'oktober-' . $year;
          break;
        case 11:
          return 'november-' . $year;
          break;
        case 12:
          return 'dezember-' . $year;
          break;
        default:
          cancel_processing('Wrong period:' . $per_type . ',' . $period . ',' . $year);
      }
    }
  }

?>
