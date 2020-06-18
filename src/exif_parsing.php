<?php

  function exif_get_count_tagstart($list, $tag) {
    // counts the number of tags in list starting with tag
    $count = 0; 
    $tl = strlen($tag); // tag length
    foreach($list as $line) {
      if(strncmp( $line, $tag, $tl ) == 0) {
        $count += 1;
      }
    }
    return $count;
  }


  function exif_get_tag_value($list, $tag) {
  // from a $list of EXIF-tags (returned by exiftool -s) pick the first one
  // *starting* with $tag and return its value (after the colon, trimmed).
  // To get the exact tag add a space to $tag.
  // If the $tag starts with a ".", "=" or "?" this will be removed bevore the search.
  // If it starts with a "?", $tag_is_set will be returned if a tag was found, otherwise $tag_not_set
  // If it starts with a "=", it's value is calculated
    global $tag_is_set;
    global $tag_not_set;
    if(substr($tag,0,1) == '.') {  // Extra processing for Meta-Tag
      return exif_get_tag_value($list, substr($tag, 1));
    } elseif(substr($tag,0,1) == '?') {  // Extra processing for Meta-Tag yes/no
      if(exif_get_tag_value($list, substr($tag, 1)) !== '') {
        return $tag_is_set;
      } else {
        return $tag_not_set;
      }
    } elseif(substr($tag,0,1) == '=') {  // will be calculated
      switch ($tag) {
        case '=Month':
          return date('n', strtotime(get_any_picture_date($list))); // REVIEW: is this secure/stable?
           break;
        case '=Week':
          // return date('W', strtotime(exif_get_tag_value($list, 'CreateDate'))); // REVIEW: is this secure/stable?
          return get_picture_wp_week($list);
          break;
        case '=GPS';
          $gpstagnum = exif_get_count_tagstart($list, 'GPS');
          if($gpstagnum == 0) {
            return $tag_not_set;
          }
          if( ($gpstagnum == 1) && (exif_get_tag_value($list, 'GPSVersionID') !== '') ) {
            return $tag_not_set;
          } else {
            return $tag_is_set;
          } 
          break;
          case '=LongestSide':
            return get_longest_side($list);
            break;  
        default:
            return 'n/a';
      }
    } else {
      $tl = strlen($tag); // tag length
      foreach($list as $line) {
        if(strncmp( $line, $tag, $tl ) == 0) {
          return trim( substr($line, strpos($line,':')+1) );
        }
      }
      return '';
    }
  }


  function scale_to($to, $me, $other) {
    // If $me is scaled $to, then the $other side is scaled to return value
    return (int) ( $other / ( ( $me * 1.0 ) / $to ) );  // must convert to float (* 1.0) and back to int
  }


  function get_exif_data($filename, $html_table = FALSE) {
    // reads EXIF data using exiftool -s (-s uses the technical names)
    // returns: list of exif data

    // PHP function "exif_read_data" doesn't read all tags, i.e. "Title", so this is deactivated.
    //$exif_data = exif_read_data($new_path, "FILE,COMPUTED,ANY_TAG,IFDO,COMMENT,EXIF", true);
    //if($debugging == true) { print_r($exif_data); };
    
    global $exiftool_command;
    if($html_table) {
      $cmd = $exiftool_command . ' -h -G -s ' . escapeshellarg($filename);
    } else {
      $cmd = $exiftool_command . ' -s ' . escapeshellarg($filename);
    }
    exec($cmd, $exif_data, $exiftool_result);
    if(false == true) { // debug
      echo "<p>filename: "; print_r($filename);
      echo "<br>exif_data: <br><pre>"; print_r($exif_data); echo "</pre>";
      echo "<br>exiftool_result: "; print_r($exiftool_result);
      echo "</p>";
    }
    if($exiftool_result !== 0) { 
      global $user;
      log_command_result($cmd, $exiftool_result, $exif_data, $user);
      cancel_processing('Fehler beim Aufruf des EXIF-Tools!'); 
    }  
    return $exif_data;
  }


  // get longest side of picture
  function get_longest_side($exif_data) {
    $pic_width  = exif_get_tag_value($exif_data, 'ImageWidth');
    $pic_height = exif_get_tag_value($exif_data, 'ImageHeight');
    if($pic_width > $pic_height) {
      return $pic_width;
    } else {
      return $pic_height;
    }
  }


  // displays EXIF data, given by the $requested map, compared to the existing $exif_data.
  //  - 
  // returns: true when everything is ok, or false if some requested tags don't match
  function exif_display($exif_data, $requested, $exif_data_orig, $complain) {

    global $user;

    // Important Tags
    $must_be_ok = array( 'ImageDescription', '=Week', '=Month', '=LongestSide' );
    // Display comparisom table
    echo PHP_EOL . '<p><div class="hideable"><table style="border:1">' . PHP_EOL;
    echo "<tr><th>Meta Daten</th><th>des hochgeladenen Bildes</th><th>des bearbeiteten Bildes</th><th>wie diese sein sollten</th>"; 
    if($complain) { echo '<th>?</th>'; } 
    echo "</tr>" . PHP_EOL;
    $all_good = true;
    foreach($requested as $exif_tag=>$exif_value) {
      $exif_tag_is  = exif_get_tag_value($exif_data, $exif_tag);
      $exif_tag_was = exif_get_tag_value($exif_data_orig, $exif_tag);
      echo "<tr><td>" . htmlspecialchars($exif_tag) . "</td><td>" . htmlspecialchars($exif_tag_was) .
           "</td><td>" . htmlspecialchars($exif_tag_is) . "</td><td>" . htmlspecialchars($exif_value); 
      if($complain) {
        echo "</td><td>";
        if( ($exif_value == '') || 
            ( ( ( $exif_tag    == '?GPS' ) || ( $exif_tag   == '=GPS' ) ) &&
                ( $exif_tag_is == 'nein'   &&   $exif_value == 'ja'   ) )    ) { 
          echo '-'; 
        } elseif($exif_tag == '=Week' && $exif_tag_is == 0) { 
          // REVIEW: in case there is no CreateDate there is no week - let's accept this for now
          echo '-'; 
        } elseif($exif_tag == '=LongestSide') {
          if($exif_tag_is >= 2000 and $exif_tag_is <=2048 ) {
            echo '✅'; 
          } else {
            $all_good = false;
            echo '🛑';
            log_usage('2W',$user,'Not OK: ' . $exif_tag . ' is: ' . $exif_tag_is . ' should: 2000-2048');
          }
        } elseif($exif_tag == 'ImageDescription') {  // ignore case when comparing ImageDescription, so user name case is irrelevant in expert mode.
          if(strtolower(trim($exif_tag_is)) == strtolower(trim($exif_value))) {
            echo '✅'; 
          } else {
            $all_good = false;
            echo '🛑';
            log_usage('2W',$user,'Not OK: ' . $exif_tag . ' is: ' . $exif_tag_is . ' should: ' . $exif_value);
          }
        } elseif(trim($exif_tag_is) == trim($exif_value)) { 
          echo '✅'; 
        } else {  
          log_debug('exif_display,tag ' . $exif_tag . ' in must_be_ok, result', array_search( $exif_tag, $must_be_ok ));
          if(array_search( $exif_tag, $must_be_ok ) === FALSE){
            echo '⚠️';
          } else {
            $all_good = false;
            echo '🛑';
            log_usage('2W',$user,'Not OK: ' . $exif_tag . ' is: ' . $exif_tag_is . ' should: ' . $exif_value);
          }
        }
      }
      echo "</td></tr>" . PHP_EOL;
    }
    echo "</table></div></p>" . PHP_EOL;
    

    // link GPS data to OSM
    $geocoordinates = exif_get_tag_value($exif_data_orig, 'GPSPosition');
    if($geocoordinates <> '') {
      $geocoordinates = str_ireplace ( ' deg' , '°' , $geocoordinates );
      $urlgeocoordinates = urlencode($geocoordinates);
      echo 'Die Geokoordinaten des Bildes ' .
         '<a href="https://www.openstreetmap.org/search?query=' . $urlgeocoordinates .
         '" target="_blank">' . $geocoordinates . '</a> (Link in neuem Fenster zu Openstreetmap.org)';
    }
    
    return $all_good;
  }

  function get_any_title($tags, $user) {
    $title_tags = array( 
      "ImageDescription",  // user / title combined - EXIF (bot)
      "Title",             // XMP
      "ObjectName"         // IPTC 
    );
    $i = 0;
    foreach($title_tags as $tt) {
      $title = exif_get_tag_value($tags, $tt);
      if($title != '') {
        // check if there is the username before a / slash
        if( ! ( strpos($title, '/') === FALSE ) ) {
          list($part1, $part2) = explode('/', $title, 2);
          if ( strtoupper(trim($user)) == strtoupper(trim($part1)) ) {
            log_debug('get_any_title, Tag', $tt . ', Index:' . $i . ', Value:' . $title . ', Title:' . $part2 );
            return trim($part2);
          }
        }
        log_debug('get_any_title, Tag', $tt . ', Index:' . $i . ', Value=Title:' . $title);
        return trim($title);
      }
      $i += 1;  
    }
    log_debug('get_any_title, No title found.', '');
    return '';
  }


?>
