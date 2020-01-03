<?php

  // Load User
  function load_user() {
    global $debugging;
    global $user_file;

    if(file_exists($user_file) == FALSE){
        cancel_processing("Missing user file.");
    }

    $user_lines = explode(PHP_EOL, file_get_contents($user_file));
    foreach ($user_lines as $line) {
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

    if($debugging) { // debug
      echo "<p>User: "; print_r($user); 
      //echo "<br>picdates['result']: "; print_r($picdates['result']); 
      echo "</p>";
    }

    return $user;
  }

  function get_user($userid, $users) {
    $indexname = strtoupper(trim($userid));
    if(array_key_exists($indexname, $users)){
      return $users[$indexname];
    } else {
      return FALSE;
    }
  }

?> 