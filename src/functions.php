<?php

  // Processing is stoped with "die", closing <body> and <hmtl> tags.
  function cancel_processing($msg) {
    echo "<p><strong>🛑 " . $msg . "</strong><br/>";
    echo "<em>Die Verarbeitung wird abgebrochen.</em></p>";
    echo '<p>Gehe an den <a href="index.php">Anfang</a> zurück um es noch einmal zu probieren.</p>';
    echo '</body></hmtl>';
    log_usage('-A', 'na', $msg);
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


  function strip_leading_zero($x) {
    if(substr($x, 0, 1) == '0') {
      return substr($x, 1);
    } else {
      return $x;
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
    log_usage('-E', $user, 'Exec Command Error');
  }

  function log_usage($page, $user, $info = '') {
    global $usage_log;
    global $usage_logging;
    if($usage_logging == 0) { return; }
    if($usage_logging == 1) {
      $log_msg = date("c") . ';' . $page . ';;' . $info . PHP_EOL ;
    } else {
      $log_msg = date("c") . ';' . $page . ';' . $user . ';' . $info . PHP_EOL ;
    }
    if(file_put_contents($usage_log, $log_msg, FILE_APPEND) === FALSE) {
      echo "<p>⚠️ Problem bei Log schreiben</p>";
    }
  }

  function log_debug($log_msg, $log_var) {
    global $debugging;
    global $debugging2;
    global $debug_log;
    if($debugging == TRUE || $debugging2 == TRUE) {
      if(file_put_contents($debug_log, $log_msg . ':' . print_r($log_var, TRUE) . PHP_EOL, FILE_APPEND) === FALSE) {
        echo "<p>⚠️ Problem bei Debug-Log schreiben</p>";
      }
    }
  }

  
  // get any picture creation date info as an array with the keys:
  // - tag  : which date tag was found, empty if none was found
  // - prio : the index of the found tag, starting with 0, 99 if none was found
  // - date : the found date-string, 'nodate' if none was found
  function get_any_picture_date_info($tags) {
    $date_tags = array( 
      "CreateDate",                  // EXIF
      "DateTimeOriginal",            // EXIF (bot)
      "DateCreated",                 // XMP
      "DigitalCreationDate",         // IPTC 
      "DateTimeCreated",             // Composite
      "SubSecCreateDate",            // Composite
      "SubSecDateTimeOriginal",      // Composite
      "ModifyDate",                  // EXIF
      "SubSecModifyDate",            // Composite
      // "FileModifyDate"               // FILE (does only work from monday - friday)
    );
    $i = 0;
    foreach($date_tags as $dt) {
      $date = exif_get_tag_value($tags, $dt);
      if($date != '') {
        log_debug('get_any_picture_date, Tag', $dt . ', Index:' . $i . ', Value:' . $date);
        return ['tag' => $dt, 'prio' => $i, 'date' => substr($date, 0, 19)];
      }
      $i += 1;  
    }
    log_debug('get_any_picture_date, No date found.', '');
    return ['tag' => '', 'prio' => 99, 'date' => 'nodate'];
  }


  // get any picture creation date  
  function get_any_picture_date($tags) {
    $da = get_any_picture_date_info($tags);
    return $da['date'];
  }


  function get_picture_date($tags) {
    $exif_create_date = get_any_picture_date($tags);
    if(($exif_create_date == '') or ($exif_create_date == 'nodate')) {
      return 'nodate';
    } else {
      return DateTime::createFromFormat('Y:m:d G:i:s', substr($exif_create_date,0,19));
    }
  }


  // weekly pic picture week is shifted by 2 days in the future 
  function get_picture_wp_week($tags) {
    $picdate = get_picture_date($tags);
    log_debug('get_any_picture_wp_week, picdate', print_r($picdate, TRUE));
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
      // return date('Y'); // BUG: This is not year change proof
    } else {
      return $picdate->format('Y');
    }
  }
    
  
  // get year of week from last day of week
  function get_picture_year_of_week($tags) {
    global $debugging;
    $picdates = picture_dates($tags);
    log_debug("get_picture_year_of_week, picdates", $picdates); 
    log_debug("get_picture_year_of_week, picdates['result']", $picdates['result']); 
    if( $picdates['result'] != 'ok') {
      return 0;
      // return date('Y'); // BUG: This is not year change proof
    } else {
      return $picdates['wp_week_end_date']->format('Y');
      log_debug("get_picture_year_of_week, picdates[wp_week_end_date]", $picdates['wp_week_end_date']); 
      log_debug("get_picture_year_of_week,  ->format(Y)", $picdates['wp_week_end_date']->format('Y'));   
    }
  }


  // calculate several date parts - currently not used
  function picture_dates($tags) {
    global $debugging;
    $returns['result'] = 'ok';
    // get CreateDate tag
    $exif_create_date = get_any_picture_date($tags);
    log_debug("picture_dates,exif_create_date", $exif_create_date);
    if($exif_create_date === 'nodate') {
      $returns['result'] = 'Error: Picture has no create date!';
      return $returns;
    }
    // convert Tag to date (CreateDate : 2019:05:15 22:58:54)
    $returns['date']  = DateTime::createFromFormat('Y:m:d G:i:s', $exif_create_date);
    log_debug("picture_dates,returns[date]: ", $returns['date']);
    // $returns['month'] = $returns['date']->format('m'); 
    // $returns['week']  = $returns['date']->format('W'); 
    // $returns['year']  = $returns['date']->format('Y'); 
    $dayinweek = $returns['date']->format('w');  // Sunday = 0 ... Saturday = 6
    log_debug("picture_dates,dayinweek", ($dayinweek));
    if ($dayinweek < 6) {
      $tmpdate = clone $returns['date'];
      log_debug("picture_dates,dayinweek<6,tmpdate", $tmpdate);
      $returns['wp_week_start_date'] = $tmpdate->sub(new DateInterval('P'.($dayinweek+1).'D'));
      log_debug("picture_dates,dayinweek<6,wp_week_start_date", ($returns['wp_week_start_date']));
      $tmpdate = clone $returns['date'];
      log_debug("picture_dates,dayinweek<6,tmpdate", $tmpdate);
      $returns['wp_week_end_date']   = $tmpdate->add(new DateInterval('P'.(5-$dayinweek).'D'));
      log_debug("picture_dates,dayinweek<6,wp_week_end_date", $returns['wp_week_end_date']);
    } else { // = 6 = saturday
      $returns['wp_week_start_date'] = $returns['date']; 
      $tmpdate = clone $returns['date'];
      $returns['wp_week_end_date']   = $tmpdate->add(new DateInterval('P6D'));
      log_debug("picture_dates,dayinweek=6,wp_week_start_date", ($returns['wp_week_start_date']));
      log_debug("picture_dates,dayinweek=6,wp_week_end_date", ($returns['wp_week_end_date']));
    }
    // $returns['wp_year_start_date'] = 
    // $returns['wp_year_end_date']   = 

    // BUG: This is not the last day of the real week! Therefore a wrong year could be determinded.
    //      In case that 1st of January is Saturday or Sunday, this will break. Because WeeklyPic Week ends on Friday.
    $returns['wp_week']            = $returns['wp_week_end_date']->format('W');
    log_debug("picture_dates,wp_week", ($returns['wp_week']));
    return $returns;
  }


  function uploadWPdir($per_type, $period, $year) { // returns path
    global $user;
    if($year < 2020) { 
      log_usage('-E', $user, 'Problem: Year ' . $year . ' used.');
      $year = date('Y'); 
    }
    if( $per_type == 'w' OR $per_type == 'W') {
      return $year . '-woche-' . strip_leading_zero($period);
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
