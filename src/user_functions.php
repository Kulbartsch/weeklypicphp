<?php

  // Load User
  function load_user() {
    global $debugging;
    global $user_file;

    if(file_exists($user_file) == FALSE){
        cancel_processing("Missing user file.");
    }

    $user_lines = explode("\n", file_get_contents($user_file));  // PHP_EOL
    log_debug('load_user,user_lines', $user_lines);
    log_debug('load_user,processing lines:', '');
    foreach ($user_lines as $line) {
      if((strlen(trim($line)) == 0) or (substr(trim($line),0,1) == '#')) { // ignore empty and comment lines
        log_debug('load_user,ignored line', $line);
      } else {
        log_debug('load_user,use line', $line);
        $parts = explode(";", $line, 3);
        $userid = trim($parts[0]);
        if(array_key_exists(1, $parts)){
          $called = trim($parts[1]);
        } else {
          $called = $userid;
        }
        $indexname = strtoupper($userid);
        $user[$indexname]["userid"] = $userid;
        $user[$indexname]["called"] = htmlspecialchars($called);
      }
    }

    if($debugging) { // debug
      echo "<p>User: "; print_r($user); 
      echo "</p>";
    }

    return $user;
  }

  function get_user($userid, $users) {
    log_debug('get_user,userid', $userid);
    $indexname = strtoupper(trim($userid));
    log_debug('get_user,indexname', $indexname);
    if(array_key_exists($indexname, $users)){
      log_debug('get_user,user-struct', $users[$indexname]);
      return $users[$indexname];
    } else {
      log_debug('get_user,not-found in', $users);
      return 'not_found';
    }
  }

?> 