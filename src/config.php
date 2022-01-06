<?php

  // PHPStorm IDE hints for variables used in including source file
  /** @var $debugging bool    */
  /** @var $debugging2  bool    */
  /** @var $config_dir  string    */
  /** @var $app_base_dir  string    */
  /** @var $cookie_name string    */
  /** @var $cookie_split  string    */
  /** @var $cookie_expires  string    */
  /** @var $tag_is_set  string    */
  /** @var $tag_not_set string    */
  /** @var $pushing_pic int   */
  /** @var $push_cloud  int   */
  /** @var $push_filesystem int   */
  /** @var $push_ftp  int   */

  /** @var $upload_server string    */
  /** @var $upload_login  string    */
  /** @var $usage_logging int   */
  /** @var $upload_folder string    */
  /** @var $command_log string    */
  /** @var $usage_log string    */
  /** @var $debug_log string    */
  /** @var $access_log  string    */
  /** @var $user_file string    */
  /** @var $convert_command string    */
  /** @var $exiftool_command  string    */
  /** @var $curl_command  string    */
  /** @var $lc_ctype  string    */
  /** @var $destination_folder  string    */
  /** @var $ftp_exec  string    */
  /** @var $check_dir string    */
  /** @var $check_user  string    */
  /** @var $slack_api_token string    */

  $debugging        = FALSE;  
  $debugging2       = FALSE;   // debug to file  

  $config_dir       = __DIR__;
  $app_base_dir     = substr($config_dir, 0, -4); // remove '/src' from the end

  $cookie_name      = "WeeklyPicPHPParam";
  $cookie_split     = "§%§";
  $cookie_expires   = time() + 60 * 60 * 24 * 100;  // now + 100 days (in seconds)

  $tag_is_set       = 'ja';
  $tag_not_set      = 'nein';

  $pushing_pic      = 0;  // 0=nothing & 1=cloud-upload & 2=filesystem & 4=FTP
  $push_cloud       = 1;
  $push_filesystem  = 2;
  $push_ftp         = 4;

  date_default_timezone_set('Europe/Berlin');  // see https://www.php.net/manual/en/timezones.php

  if($debugging) { // debug
    echo "<p>⚠️ DEBUGGING IS SET TO TRUE! DON'T DO THIS ON A PUBLIC SERVER! ⚠️</p>";
  }

  // The upload server is a secret - you must create a file with just the URL manually!
  // The file must contain at least the first two lines to enable upload.
  // There must be *no* space between the parametername and the '=' !
  // ----
  // server=<URL of the server>         // URL to NextCloud or OwnCloud Server
  // login=<password>                   // Login to NextCloud or OwnCloud Server as URL addition
  // loglevel=<n>                       // 0=no logging, 1=only pages, 2=and user, 3=and anonymize IP, 4=and full IP
  // upload_folder=<foldername>         // Das Upload-Verzeichnis
  // command_log=<filename>
  // usage_log=<filename>
  // access_log=<filename>
  // user_file=<filename>               // file with participants, formatted <slack-id>[;<call-as>]
  // convert_command=<programname>      // imagemagick convert
  // exiftool_command=<programname>     // EXIFtool
  // curl_command=<programname>         // curl
  // lc_ctype=<os_locale>               // locale matching OS available locale, should be UTF8
  // destination_folder=<foldername>    // Zielverzeichnis für die Ablage des Bildes - if folder is not set it's not used
  // ftp_exec=<full ftp command with parameters> // $file$ , $fqfn$ (full qualified filename) and $dir$ will be replaced
  // check_dir=<check_dir>              // directory to transfer pictures to check to
  // debugging2=<TRUE|FALSE>            // write to debug file
  // check_user=<ON|OFF>                // hard check username - if switched on and user is unknown processing is cancelled
  // slack_api_token=<SLACK_API_TOKEN>  // slack API token
  // ----
  // Of course you could set the parameters directly here as well - but that's
  // not handy if you use github. ;)
  // Don't forget to put the upload_server.config into the .gitignore file!!!

  $upload_server      = 'na';     // Next/Own-Cloud Server
  $upload_login       = 'na';
  $usage_logging      =  99;      //  99=unset - named "loglevel" in config file 
  $upload_folder      = 'na';     // Das Upload-Verzeichnis
  $command_log        = 'na';
  $usage_log          = 'na';
  $debug_log          = 'na';
  $access_log         = 'na';
  $user_file          = 'na';
  $convert_command    = 'na';     // imagemagick convert
  $exiftool_command   = 'na';     // EXIFtool
  $curl_command       = 'na';     // curl
  $lc_ctype           = 'na'; 
  $destination_folder = 'na';
  $ftp_exec           = 'na';
  $check_dir          = 'na';
  $check_user         = 'na';
  $slack_api_token    = 'na';

  $upload_server_f  = $config_dir . '/config.config'; 
 
  if (file_exists($upload_server_f)) {
    $server_config_lines = explode(PHP_EOL, file_get_contents($upload_server_f));
    foreach ($server_config_lines as $line) {

      if(substr($line, 0, 7) == 'server=') {
        if($upload_server == 'na') {
          $upload_server = trim(substr($line, 7));
        } else {
          echo '<p>⚠️ Error in Upload-Server-Configuration, server already defined.</p>';
        }
      } elseif (substr($line, 0, 6) == 'login=') {
        if($upload_login == 'na') {
          $upload_login = trim(substr($line, 6));
        } else {
          echo '<p>⚠️ Error in Upload-Server-Configuration, login already defined.</p>';
        }
      } elseif (substr($line, 0, 9) == 'loglevel=') {
        if($usage_logging == 99) {
          $usage_logging = intval(trim(substr($line, 9)));
        } else {
          echo '<p>⚠️ Error in Upload-Server-Configuration, loglevel already defined.</p>';
        }
      } elseif (substr($line, 0, 14) == 'upload_folder=') {
        if($upload_folder == 'na') {
          $upload_folder = trim(substr($line, 14));
        } else {
          echo '<p>⚠️ Error in Upload-Server-Configuration, upload_folder already defined.</p>';
        }
      } elseif (substr($line, 0, 12) == 'command_log=') {
        if($command_log == 'na') {
          $command_log = trim(substr($line, 12));
        } else {
          echo '<p>⚠️ Error in Upload-Server-Configuration, command_log already defined.</p>';
        }                            //  12345678901234567 
      } elseif (substr($line, 0, 10) == 'usage_log=') {
        if($usage_log == 'na') {
          $usage_log = trim(substr($line, 10));
        } else {
          echo '<p>⚠️ Error in Upload-Server-Configuration, usage_log already defined.</p>';
        }                            //  12345678901234567 
      } elseif (substr($line, 0, 11) == 'access_log=') {
        if($access_log == 'na') {
          $access_log = trim(substr($line, 11));
        } else {
          echo '<p>⚠️ Error in Upload-Server-Configuration, access_log already defined.</p>';
        }                            //  12345678901234567 
      } elseif (substr($line, 0, 10) == 'debug_log=') {
        if($debug_log == 'na') {
          $debug_log = trim(substr($line, 10));
        } else {
          echo '<p>⚠️ Error in Upload-Server-Configuration, debug_log already defined.</p>';
        }                            //  12345678901234567 
      } elseif (substr($line, 0, 10) == 'user_file=') { 
        if($user_file == 'na') {
          $user_file = trim(substr($line, 10));
        } else {
          echo '<p>⚠️ Error in Upload-Server-Configuration, user_file already defined.</p>';
        }
      } elseif (substr($line, 0, 16) == 'convert_command=') {
        if($convert_command == 'na') {
          $convert_command = trim(substr($line, 16));
        } else {
          echo '<p>⚠️ Error in Upload-Server-Configuration, convert_command already defined.</p>';
        }                            //  12345678901234567 
      } elseif (substr($line, 0, 17) == 'exiftool_command=') {
        if($exiftool_command == 'na') {
          $exiftool_command = trim(substr($line, 17));
        } else {
          echo '<p>⚠️ Error in Upload-Server-Configuration, exiftool_command already defined.</p>';
        }
      } elseif (substr($line, 0, 13) == 'curl_command=') {
        if($curl_command == 'na') {
          $curl_command = trim(substr($line, 13));
        } else {
          echo '<p>⚠️ Error in configuration, curl_command already defined.</p>';
        }
      } elseif (substr($line, 0, 9) == 'lc_ctype=') {
        if($lc_ctype == 'na') {
          $lc_ctype = trim(substr($line, 9));
        } else {
          echo '<p>⚠️ Error in configuration, lc_ctype already defined.</p>';
        }                            //  1234567890123456789012345
      } elseif (substr($line, 0, 19) == 'destination_folder=') {
        if($destination_folder == 'na') {
          $destination_folder = trim(substr($line, 19));
        } else {
          echo '<p>⚠️ Error in configuration, destination_folder already defined.</p>';
        }                           //  1234567890123456789012345
      } elseif (substr($line, 0, 9) == 'ftp_exec=') {
        if($ftp_exec == 'na') {
          $ftp_exec = trim(substr($line, 9));
        } else {
          echo '<p>⚠️ Error in configuration, ftp_exec already defined.</p>';
        }                            //  1234567890123456789012345
      } elseif (substr($line, 0, 10) == 'check_dir=') {
        if($check_dir == 'na') {
          $check_dir = trim(substr($line, 10));
        } else {
          echo '<p>⚠️ Error in configuration, check_dir already defined.</p>';
        }                            //  1234567890123456789012345
      } elseif (substr($line, 0, 16) == 'slack_api_token=') {
        if($slack_api_token == 'na') {
          $slack_api_token = trim(substr($line, 16));
        } else {
          echo '<p>⚠️ Error in configuration, slack_api_token already defined.</p>';
        }                            //  1234567890123456789012345
      } elseif (substr($line, 0, 11) == 'check_user=') {
        if($check_user == 'na') {
          $check_user = strtoupper(trim(substr($line, 11)));
          if(($check_user != 'ON') and ($check_user != 'OFF')) {
            $check_user = 'na';
            echo '<p>⚠️ Error in configuration, illegal value, check_user: ' . $line . '</p>';
          }
        } else {
          echo '<p>⚠️ Error in configuration, check_user already defined.</p>';
        }                           //  1234567890123456789012345
      } elseif (substr($line, 0, 11) == 'debugging2=') {
          switch(trim(substr($line, 11))) {
            case 'FALSE':
              $debugging2 = FALSE;
              // log_debug('config.php,debugging2', $debugging2);
              break;
            case 'TRUE':
              $debugging2 = TRUE;
              // log_debug('config.php,debugging2', $debugging2);
              break;
            default:
              // log_debug('config.php,illegal debugging2 parameter', $line);
          }
      }
      
    }

  } else {
    echo '<p>⚠️ Server-Configuration file is missing!</p>';
  }

  // $pushing_pic      = 0;  // 0=nothing & 1=cloud-upload & 2=filesystem & 4=FTP
  // $push_cloud       = 1;
  // $push_filesystem  = 2;
  // $push_ftp         = 4;
  if($upload_server <> 'na' AND $upload_login <> 'na') {
    $pushing_pic = $pushing_pic | $push_cloud;
  } 
  if($destination_folder <> 'na') {
    $pushing_pic = $pushing_pic | $push_filesystem;
  } 
  if($ftp_exec <> 'na' ) {
    $pushing_pic = $pushing_pic | $push_ftp;
  } 

  // Set Defaults - if not imported from file
  if($usage_logging == 99)      { $usage_logging    = 1; }           // Default, log pages called
  if($upload_folder == 'na')    { $upload_folder    = '_files/'; }   // Das Upload-Verzeichnis
  if($command_log == 'na')      { $command_log      = '_log/exec_cmd.log'; }
  if($usage_log == 'na')        { $usage_log        = '_log/usage.log'; } 
  if($debug_log == 'na')        { $debug_log        = '_log/debug.log'; } 
  if($user_file == 'na')        { $user_file        = '_log/user.txt'; }
  if($access_log == 'na')       { $access_log       = '_log/access.log'; } 
  if($convert_command == 'na')  { $convert_command  = '/usr/local/bin/convert'; }    // imagemagick convert
  if($exiftool_command == 'na') { $exiftool_command = '/usr/local/bin/exiftool'; }   // EXIFtool
  if($curl_command == 'na')     { $curl_command     = '/usr/bin/curl'; }             // curl
  if($lc_ctype == 'na')         { $lc_ctype         = 'en_US.UTF-8'; }
  if($check_dir == 'na')        { $check_dir        = '00_check'; }
  if($check_user == 'na')       { $check_user       = 'ON'; }

  setlocale(LC_CTYPE, $lc_ctype);

  if($pushing_pic == 0) {
    echo '<p>Es ist kein automatischer Upload zu WeekyPic möglich. Dies kann nur manuell (Download+Upload) erfolgen.</p>';
  }

  if($debugging) { // debug
    echo "<p>server: "; print_r($upload_server);
    echo "<br>login: "; print_r($upload_login);
    echo "<br>ftp_exec: "; print_r($ftp_exec);
    echo "<br>pushing_pic: "; print_r($pushing_pic);
    echo "<br>exiftool_command: "; print_r($exiftool_command);
    echo "</p>";
  }

?>
