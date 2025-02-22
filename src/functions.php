<?php

// Processing is stoped with "die", closing <body> and <hmtl> tags.
function cancel_processing($msg)
{
    echo "<p><strong>🛑 " . $msg . "</strong><br/>";
    echo "<em>Die Verarbeitung wird abgebrochen.</em></p>";
    echo '<p>Gehe an den <a href="index.php">Anfang</a> zurück um es noch einmal zu probieren.</p>';
    echo '</body></hmtl>';
    log_usage('-A', '--', $msg);
    die();
}


function validate_number_and_return_string($n, $min, $max)
{
    $num = intval($n);
    if ($num < $min or $num > $max) {
        cancel_processing("Fehler! Wert $n ist nicht im Bereich $min - $max !");
    }
    return sprintf('%02d', $num);
}


function sanitize_input($param, $required)
{
    if (empty($_POST[$param])) {
        if ($required) {
            cancel_processing("Parameter '$param' wurde nicht angegeben.");
        } else {
            return '';
        }
    } else {
        return trim($_POST[$param]);
        // return htmlspecialchars(trim($_POST[$param]));
    }
}


function strip_leading_zero($x)
{
    if (substr($x, 0, 1) == '0') {
        return substr($x, 1);
    } else {
        return $x;
    }
}


function string_starts_with($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}


function leading_zeros($string, $len)
{
    return str_pad($string, $len, '0', STR_PAD_LEFT);
}


// --------------------------- //

function delete_file($filename)
{
    if (file_exists($filename)) {
        if (unlink($filename) == false) {
            echo '<p>⚡️ Fehler beim Löschen der Bild-Datei. Das sollte nicht passieren.</p>';
            echo '<p>Bitte informiere einen Admin über das Problem.</p>';
            return FALSE;
        } else {
            if (file_exists($filename)) {
                echo '<p>⚡️ Fehler beim Löschen der Bild-Datei. (2)</p>';
                echo '<p>Bitte informiere einen Admin über das Problem.</p>';
                return FALSE;
            } else {
                // echo '<p>♻️ Das Bild wurde von diesem Server gelöscht.</p>';
                return TRUE;
            }
        }
    } else {
        echo '<p>⚡️ Die zu löschende Bild-Datei existiert nicht. 🤔</p>';
        echo '<p>Bitte informiere einen Admin über das Problem.</p>';
        return FALSE;
    }
}


function move_file($filename, $destination, $use_basename = FALSE)
{
    if (file_exists($filename)) {
        if ($use_basename) {
            if (substr($destination, -1) == '/') {
                $moveto = $destination . basename($filename);
            } else {
                $moveto = $destination . '/' . basename($filename);
            }
        } else {
            $moveto = $destination;
        }
        if (file_exists($moveto)) {
            // echo '<p>Ein Bild mit diesem Namen existiert schon WeeklyPic Eingangs-Verzeichnis. Das vorhandene Bild wird gelöscht und durch das neue ersetzt. </p>';
            delete_file($moveto);
        }
        log_debug('Moving file (2)', $filename . ' to ' . $moveto);
        exec('pwd', $out, $rc);
        log_debug('pwd', $out[0]);
        if (rename($filename, $moveto) == false) {
            echo '<p>⚡️ Fehler beim Verschieben der Bild-Datei (1). Das sollte nicht passieren. </p>';
            echo '<p>Bitte informiere einen Admin über das Problem.</p>';
            log_debug('Error moving file (2)', $filename . ' to ' . $moveto);
            return FALSE;
        } else {
            if (file_exists($filename)) {
                log_debug('Error moving file (3)', $filename . ' to ' . $moveto);
                echo '<p>⚠️ Fehler beim Verschieben der Bild-Datei. (2) Das sollte nicht passieren. </p>';
                echo '<p>Bitte informiere einen Admin über das Problem.</p>';
                return FALSE;
            } else {
                // echo '<p>✅ Das Bild wurde ins WeeklyPic Eingangs-Verzeichnis verschoben.</p>';
                return TRUE;
            }
        }
    } else {
        echo '<p>⚠️ Die Bild-Datei existiert nicht (mehr). 🤔 Das sollte nicht passieren.';
        echo 'Oder hast du diese Seite neu geladen? Dann passiert das.</p>';
        echo '<p>Ansonsten informiere bitte einen Admin über das Problem.</p>';
        return FALSE;
    }
}


// ---------------------- //
// Logging

function log_command_result($cmd, $result, $output, $user)
{
    global $command_log;
    $log_msg = PHP_EOL . 'time:' . date("c") . ';' . $user . PHP_EOL .
        'command: ' . $cmd . PHP_EOL . 'result: ' . $result . PHP_EOL .
        'output:' . PHP_EOL . print_r($output, TRUE) . PHP_EOL .
        '-- END --' . PHP_EOL . PHP_EOL;
    if (file_put_contents($command_log, $log_msg, FILE_APPEND) === FALSE) {
        echo "<p>⚠️ Problem bei Command-Log schreiben</p>";
    }
    log_usage('-E', $user, 'Exec Command Error');
}

function log_usage($page, $user, $info = '', $loguse = TRUE, $logacs = FALSE)
{
    global $usage_log;
    global $access_log;
    global $usage_logging;

    switch ($usage_logging) {
        case 0:
            return;
        case 1:
            $log_msg = date("c") . ';' . $page . ';;' . $info . PHP_EOL;
            $log_acs = $log_msg;
            break;
        case 2:
            $log_msg = date("c") . ';' . $page . ';' . $user . ';' . $info . PHP_EOL;
            $log_acs = $log_msg;
            break;
        case 3;
            // TODO: implement anonymized IP logging
        case 4;
            $log_msg = date("c") . ';' . $page . ';' . $user . ';' . $info . PHP_EOL; // like 2
            $log_acs = date("c") . ';' . $page . ';' . $user . ',' . get_ip_address() . ';' . $info . PHP_EOL;
            break;
        default: // like 1
            $log_msg = date("c") . ';' . $page . ';;' . $info . PHP_EOL;
            $log_acs = $log_msg;
    }
    if ($loguse) {
        if (file_put_contents($usage_log, $log_msg, FILE_APPEND) === FALSE) {
            echo "<p>⚠️ Problem bei Usage-Log schreiben</p>";
        }
    }
    if ($logacs) {
        if (file_put_contents($access_log, $log_acs, FILE_APPEND) === FALSE) {
            echo "<p>⚠️ Problem bei Access-Log schreiben</p>";
        }
    }
}

function log_debug($log_msg, $log_var)
{
    global $debugging;
    global $debugging2;
    global $debug_log;
    if ($debugging == TRUE || $debugging2 == TRUE) {
        if (file_put_contents($debug_log, $log_msg . ':' . print_r($log_var, TRUE) . PHP_EOL, FILE_APPEND) === FALSE) {
            echo "<p>⚠️ Problem bei Debug-Log schreiben</p>";
        }
    }
}

// IP functions from
// https://stackoverflow.com/questions/1634782/what-is-the-most-accurate-way-to-retrieve-a-users-correct-ip-address-in-php

/**
 * Retrieves the best guess of the client's actual IP address.
 * Takes into account numerous HTTP proxy headers due to variations
 * in how different ISPs handle IP addresses in headers between hops.
 */
function get_ip_address()
{
    // Check for shared internet/ISP IP
    if (!empty($_SERVER['HTTP_CLIENT_IP']) && $this->validate_ip($_SERVER['HTTP_CLIENT_IP']))
        return $_SERVER['HTTP_CLIENT_IP'];

    // Check for IPs passing through proxies
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        // Check if multiple IP addresses exist in var
        $iplist = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        foreach ($iplist as $ip) {
            if ($this->validate_ip($ip))
                return $ip;
        }
    }

    if (!empty($_SERVER['HTTP_X_FORWARDED']) && $this->validate_ip($_SERVER['HTTP_X_FORWARDED']))
        return $_SERVER['HTTP_X_FORWARDED'];
    if (!empty($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']) && $this->validate_ip($_SERVER['HTTP_X_CLUSTER_CLIENT_IP']))
        return $_SERVER['HTTP_X_CLUSTER_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_FORWARDED_FOR']) && $this->validate_ip($_SERVER['HTTP_FORWARDED_FOR']))
        return $_SERVER['HTTP_FORWARDED_FOR'];
    if (!empty($_SERVER['HTTP_FORWARDED']) && $this->validate_ip($_SERVER['HTTP_FORWARDED']))
        return $_SERVER['HTTP_FORWARDED'];

    // Return unreliable IP address since all else failed
    return $_SERVER['REMOTE_ADDR'];
}

/**
 * Ensures an IP address is both a valid IP address and does not fall within
 * a private network range.
 *
 * @access public
 * @param string $ip
 */
function validate_ip(string $ip)
{
    if (filter_var($ip, FILTER_VALIDATE_IP,
            FILTER_FLAG_IPV4 |
            FILTER_FLAG_IPV6 |
            FILTER_FLAG_NO_PRIV_RANGE |
            FILTER_FLAG_NO_RES_RANGE) === false)
        return false;
    //self::$ip = $ip;
    return true;
}

// *********************************************************************************


// get any picture creation date info as an array with the keys:
// - tag  : which date tag was found, empty if none was found
// - prio : the index of the found tag, starting with 0, 99 if none was found
// - date : the found date-string, 'nodate' if none was found
function get_any_picture_date_info($tags)
{
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
    foreach ($date_tags as $dt) {
        $date = exif_get_tag_value($tags, $dt);
        if ($date != '') {
            log_debug('get_any_picture_date, Tag', $dt . ', Index:' . $i . ', Value:' . $date);
            return ['tag' => $dt, 'prio' => $i, 'date' => substr($date, 0, 19)];
        }
        $i += 1;
    }
    log_debug('get_any_picture_date, No date found.', '');
    return ['tag' => '', 'prio' => 99, 'date' => 'nodate'];
}


// get any picture creation date
function get_any_picture_date($tags)
{
    $da = get_any_picture_date_info($tags);
    return $da['date'];
}


function get_picture_date($tags)
{
    $exif_create_date = get_any_picture_date($tags);
    if (($exif_create_date == '') or ($exif_create_date == 'nodate')) {
        return 'nodate';
    } else {
        return DateTime::createFromFormat('Y:m:d G:i:s', substr($exif_create_date, 0, 19));
    }
}


// weekly pic picture week is shifted by 2 days in the future
function get_picture_wp_week($tags)
{
    $picdate = get_picture_date($tags);
    log_debug('get_any_picture_wp_week, picdate', print_r($picdate, TRUE));
    if ($picdate == 'nodate') {
        return 0;
    } else {
        //                        Period od 2 days
        return $picdate->add(new DateInterval('P2D'))->format('W');
    }
}


// get year of picture
function get_picture_year($tags)
{
    $picdate = get_picture_date($tags);
    if ($picdate == 'nodate') {
        return 0;
    } else {
        return $picdate->format('Y');
    }
}


// get year of week from last day of week
function get_picture_year_of_week($tags)
{
    global $debugging;
    $picdates = picture_dates($tags);
    log_debug("get_picture_year_of_week, picdates", $picdates);
    log_debug("get_picture_year_of_week, picdates['result']", $picdates['result']);
    if ($picdates['result'] != 'ok') {
        return 0;
    } else {
        log_debug("get_picture_year_of_week, picdates[wp_week_end_date]", $picdates['wp_week_end_date']);
        log_debug("get_picture_year_of_week,  ->format(Y)", $picdates['wp_week_end_date']->format('Y'));
        return $picdates['wp_week_year'];
    }
}


// calculate several date parts - currently not used
function picture_dates($tags)
{
    global $debugging;
    $returns['result'] = 'ok';
    // get CreateDate tag
    $exif_create_date = get_any_picture_date($tags);
    log_debug("picture_dates,exif_create_date", $exif_create_date);
    if ($exif_create_date === 'nodate') {
        $returns['result'] = 'Error: Picture has no create date!';
        return $returns;
    }
    // convert Tag to date (CreateDate : 2019:05:15 22:58:54)
    $returns['date'] = DateTime::createFromFormat('Y:m:d G:i:s', $exif_create_date);
    log_debug("picture_dates,returns[date]: ", $returns['date']);
    $returns['month'] = $returns['date']->format('m');
    $returns['week'] = $returns['date']->format('W');
    $returns['year'] = $returns['date']->format('Y');
    $dayinweek = $returns['date']->format('w');  // Sunday = 0 ... Saturday = 6
    log_debug("picture_dates,dayinweek", ($dayinweek));
    if ($dayinweek < 6) {
        $tmpdate = clone $returns['date'];
        log_debug("picture_dates,dayinweek<6,tmpdate", $tmpdate);
        $returns['wp_week_start_date'] = $tmpdate->sub(new DateInterval('P' . ($dayinweek + 1) . 'D'));
        log_debug("picture_dates,dayinweek<6,wp_week_start_date", ($returns['wp_week_start_date']));
        $tmpdate = clone $returns['date'];
        log_debug("picture_dates,dayinweek<6,tmpdate", $tmpdate);
        $returns['wp_week_end_date'] = $tmpdate->add(new DateInterval('P' . (5 - $dayinweek) . 'D'));
        log_debug("picture_dates,dayinweek<6,wp_week_end_date", $returns['wp_week_end_date']);
    } else { // = 6 = saturday
        $returns['wp_week_start_date'] = $returns['date'];
        $tmpdate = clone $returns['date'];
        $returns['wp_week_end_date'] = $tmpdate->add(new DateInterval('P6D'));
        log_debug("picture_dates,dayinweek=6,wp_week_start_date", ($returns['wp_week_start_date']));
        log_debug("picture_dates,dayinweek=6,wp_week_end_date", ($returns['wp_week_end_date']));
    }
    // if friday of wp-week is january and week > 52 -> subtract one year
    $returns['wp_week'] = $returns['wp_week_end_date']->format('W');
    log_debug("picture_dates,wp_week", ($returns['wp_week']));
    $returns['wp_week_year'] = $returns['wp_week_end_date']->format('Y');
    $returns['wp_month'] = $returns['wp_week_end_date']->format('n');
    if ($returns['wp_week'] >= 52 and $returns['wp_month'] == 1) {
        $returns['wp_week_year'] = $returns['wp_week_year'] - 1;
    }
    return $returns;
}


function guess_picture_year($period_type, $period)
{
    $this_month = date('n');
    $this_year = date('Y');
    if ($this_month == 12 && $period_type == 'W' && $period == 1) {
        return strval($this_year + 1);
    }
    if ($this_month == 1) {
        if ($period_type == 'W' && $period > 50) {
            return strval($this_year - 1);
        }
        if ($period_type == 'M' && $period == 12) {
            return strval($this_year - 1);
        }
    }
    return $this_year;
}


// fix year when period is around new year
// $period_type ('W' or 'M') and $period (1-53 or 1-12) are the original values,
// $period_year is the year to check, all against $date
// returns the corrected year
function fix_year($period_type, $period, $period_year, $date = new DateTime())
{
    // get reference month and year
    $this_month = $date->format('n');
    $this_year = $date->format('Y');
    $this_date = $date->format('Y-m-d (W)');
    log_debug("fix_year - checking   ", $period_type . " " . $period . " " . $period_year .
        " against " . $this_date);
    // log_debug("fix_year -     against M ", $this_month . " " . $this_year);

    // validate $period_year is plausible
    if ($period_year < $this_year - 1 or $period_year > $this_year + 1) {
        log_debug("fix_year -     Error, Year is not plausible:", $period_year);
        return "0000";
    }

    // check if year needs to be corrected
    if ($this_month == 12 && $period_type == 'W' && $period == 1) {
        log_debug("fix_year -     Corrected (1) to", $this_year + 1);
        return strval($this_year + 1);
    }
    if ($this_month == 1) {
        log_debug("fix_year -     January, checking", "");
        if ($period_type == 'W' && $period > 50) {
            log_debug("fix_year -     Corrected (2) to", $this_year - 1);
            return strval($this_year - 1);
        }
        if ($period_type == 'M' && $period == 12) {
            log_debug("fix_year -     Corrected (3) to", $this_year - 1);
            return strval($this_year - 1);
        }
    }

    log_debug("fix_year -     No correction needed, returning", $period_year);
    return $period_year;
}


// ----


function uploadWPdir($per_type, $period, $year)
{ // returns path
    global $user;
    if ($year < 2020) {
        log_usage('-E', $user, 'Problem: Year ' . $year . ' used.');
        $year = date('Y');
    }
    if ($per_type == 'w' or $per_type == 'W') {
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
