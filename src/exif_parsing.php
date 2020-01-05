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

  function get_exif_data($filename) {
    // reads EXIF data using exiftool -s (-s uses the technical names)
    // returns: list of exif data

    // PHP function "exif_read_data" doesn't read all tags, i.e. "Title", so this is deactivated.
    //$exif_data = exif_read_data($new_path, "FILE,COMPUTED,ANY_TAG,IFDO,COMMENT,EXIF", true);
    //if($debugging == true) { print_r($exif_data); };
    
    global $exiftool_command;
    $cmd = $exiftool_command . ' -s ' . escapeshellarg($filename);
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

  function exif_display($exif_data, $requested, $complain) {
    // displays EXIF data given by the requested hash compared to the requested data.
    // returns: true when everything is ok, or false if some requested tags don't match

    global $user;

    // Calculate new size
    $pic_width  = exif_get_tag_value($exif_data, 'ImageWidth');
    $pic_height = exif_get_tag_value($exif_data, 'ImageHeight');
    if($pic_width > $pic_height) {
      $requested['.ImageHeight'] = scale_to(2000, $pic_width, $pic_height);
      $requested['.ImageWidth']  = '2000';
    } else {
      $requested['.ImageWidth']  = scale_to(2000, $pic_height, $pic_width);
      $requested['.ImageHeight'] = '2000';
    }
    // $requested['ExifImageWidth']  = $requested['.ImageWidth'];
    // $requested['ExifImageHeight'] = $requested['.ImageHeight'];
    // Important Tags
    // BUG: Also check longest line for 2000 pix (neccessary for expert-mode)
    // BUG: Also check for ImageDescription (neccessary for expert-mode)
    $must_be_ok = array( 'ImageDescription', '=Week', '=Month' );
    // Display comparisom table
    echo '<p><table style="border:1">';
    echo "<tr><th>EXIF Tag</th><th>aktuell</th><th>soll</th>"; 
    if($complain) { echo '<th>?</th>'; }
    echo "</tr>";
    $all_good = true;
    foreach($requested as $exif_tag=>$exif_value) {
      $exif_tag_is = exif_get_tag_value($exif_data, $exif_tag);
      echo "<tr><td>$exif_tag</td><td>$exif_tag_is</td><td>$exif_value"; 
      if($complain) {
        echo "</td><td>";
        if( ($exif_value == '') || 
            ( ( ( $exif_tag    == '?GPS' ) || ( $exif_tag   == '=GPS' ) ) &&
                ( $exif_tag_is == 'nein'   &&   $exif_value == 'ja'   ) )    ) { 
          echo '-'; 
        } elseif($exif_tag == '=Week' && $exif_tag_is == 0) { 
          // REVIEW: in case there is no CreateDate there is no week - let's accept this for now
          echo '-'; 
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
      echo "</td></tr>";
    }
    echo "</table></p>";
    

    // link GPS data to OSM
    $geocoordinates = exif_get_tag_value($exif_data, 'GPSPosition');
    if($geocoordinates <> '') {
      $geocoordinates = str_ireplace ( ' deg' , '°' , $geocoordinates );
      $urlgeocoordinates = urlencode($geocoordinates);
      echo 'Die Geokoordinaten des Bildes ' .
         '<a href="https://www.openstreetmap.org/search?query=' . $urlgeocoordinates .
         '" target="_blank">' . $geocoordinates . '</a> (Link in neuem Fenster zu Openstreetmap.org)';
    }
    
    return $all_good;
  }

  function get_any_title($tags) {
    $title_tags = array( 
      "ImageDescription",  // user / title combined - EXIF (bot)
      "Title",             // XMP
      "ObjectName"         // IPTC 
    );
    $i = 0;
    foreach($title_tags as $tt) {
      $title = exif_get_tag_value($tags, $tt);
      if($title != '') {
        if($tt == 'ImageDescription') { // seperate user / title
          $title = trim(explode('/', $title, 2)[1]);
        }
        if ($title != '') {
          log_debug('get_any_title, Tag', $tt . ', Index:' . $i . ', Value:' . $title);
          return trim($title);
        }
      }
      $i += 1;  
    }
    log_debug('get_any_title, No title found.', '');
    return '';
  }


?>
