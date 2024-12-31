<?PHP

// include '../src/functions.php';

// real simple debug output
function debug($log_msg, $log_var)
{
    if (TRUE == FALSE) {
        echo PHP_EOL . '<p>INFO: ' . $log_msg . ': ' . var_export($log_var, TRUE) . '</p>';
        //echo PHP_EOL . '<p>DBG: ' . $log_msg . ': ' . print_r($log_var, TRUE) . '</p>';
    }
}


// check and create a folder if it does not exist
function create_folder($destination)
{
    global $user;
    if (!is_dir($destination)) {
        if (is_file($destination)) {
            log_usage('3E', $user, $destination . " is a file, not a directory.");
            echo '<p>⚡️ Fehler beim Verschieben der Bild-Datei. ' . $destination . ' ist eine Datei.</p>';
            echo '<p>Das sollte nicht passieren. Bitte informiere einen Admin über das Problem.</p>';
            return FALSE;
        } else {
            if (!mkdir($destination)) {
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
    log_debug('OK, directory exists.', $destination);
    return TRUE;
}


// create a period folder (if it does not exist)
function create_period_folder($destination_folder, $year, $per_type, $period)
{
    $destination = $destination_folder . '/' . leading_zeros($year, 4);
    clearstatcache();
    if (!create_folder($destination)) {
        return false;
    }
    $destination = $destination . '/' . strtoupper(substr($per_type, 0, 1));
    if (!create_folder($destination)) {
        return false;
    }
    $destination = $destination . '/' . leading_zeros($period, 2);
    if (!create_folder($destination)) {
        return false;
    }
    return $destination . '/';
}


// store folder in directory hierarchy, add sub-folder if necessary. create file with metainformation as well
// Structure is: $destination/[year]/[period type]/[period m or w]/[w|m]_[period]_[username].jpg
function store_file($pathfilename, $destination_folder, $year, $per_type, $period, $filebasename, $comment, $user, $description, $error): bool
{

    $destination = create_period_folder($destination_folder, $year, $per_type, $period);

    log_debug('store_file - pathfilename', $pathfilename);
    log_debug('store_file - destination_folder', $destination_folder);
    log_debug('store_file - year', $year);
    log_debug('store_file - per_type', $per_type);
    log_debug('store_file - period', $period);
    log_debug('store_file - filebasename', $filebasename);
    log_debug('store_file - comment', $comment);
    log_debug('store_file - user', $user);
    log_debug('store_file - description', $description);
    log_debug('store_file - error', $error);
    log_debug('store_file - destination', $destination);

    if (is_file($destination . $filebasename . '.jpg')) { // deleting existing files
        if (!unlink($destination . $filebasename . '.jpg')) {
            log_usage('I3', $user, 'Error deleting existing file ' . $destination . $filebasename . '.jpg');
        } else {
            // echo "<p>Bereits vorhandenes Bild wird gelöscht.</p>";
            log_debug('OK, removed existing file', $destination . $filebasename . '.jpg');
        }
        unlink($destination . $filebasename . '.meta');
        unlink($destination . $filebasename . '.txt');
    }

    log_debug('Moving file', $pathfilename . ' to ' . $destination . $filebasename . '.jpg');
    exec('pwd', $out, $rc);
    log_debug('pwd', $out[0]);
    if (!move_file($pathfilename, $destination . $filebasename . '.jpg')) {
        log_debug('Error moving file', $pathfilename . '->' . $destination . $filebasename . '.jpg');
        return false;
    }

    if (strlen(trim($comment)) <> 0 || strlen(trim($error)) <> 0) {
        file_put_contents($destination . "/" . $filebasename . ".txt", $comment . PHP_EOL . '[' . $error . ']' . PHP_EOL);
    }

    $metadata = array('@year:' . $year . PHP_EOL,
        '@per_type:' . $per_type . PHP_EOL,
        '@per_text:' . (($per_type == 'm') ? 'Monat' : 'Woche') . PHP_EOL,
        '@period:' . $period . PHP_EOL,
        '@user:' . $user . PHP_EOL,
        '@description:' . $description . PHP_EOL,
        '@filename:' . $filebasename . '.jpg' . PHP_EOL,
        '@error:' . $error . PHP_EOL,
        '@comment:' . $comment . PHP_EOL,
        '+one_picture.in' . PHP_EOL);
    file_put_contents($destination . "/" . $filebasename . ".meta", $metadata);

    return TRUE;
}


function find_files_to_check()
{
    $files_to_check = array();
    exec('find ../images -name  "[wm]_*.txt"', $lines, $result);
    if ($result <> 0) {
        echo '<p>FEHELR! find images gab einen Fehler zurück.</p>';
    }
    foreach ($lines as $line) {
        $files_to_check[] = array($line, file_get_contents($line));
    }
    return $files_to_check;
}


function number_of_files_to_check()
{
    return count(find_files_to_check());
}


// function moves a set of files for one picture to another period
// $pathfilname - .jpg picture name viewed from admin view like ../images/2022/W/01/w_01_alexander.xxx
// $year - move to year
// $per_type - 'M' for month or 'W' for week
// $period - 1..12 for Month or 1..52 for week
function move_picture_set($pathfilename, $year, $per_type, $period)
{
    if (!check_filepath($pathfilename)) {
        return FALSE;
    }
    $rc = TRUE;
    $fnp = filenameparts($pathfilename);
    if( $fnp === FALSE ) {
        debug('move file, error parsing pathfilename:', $pathfilename);
        return false;
    }
    // check date - not to overwrite itself
    if( ($fnp['year'] == $year) and ($fnp['pertype'] == $per_type) and  ($fnp['period'] == $period) ) {
        debug('move file, would overwrite itself:', $pathfilename, ' to ', $year, '/', $per_type, '/', $period);
        return FALSE;
    }
    $destination = '../images/';
    $destination = create_period_folder($destination, $year, $per_type, $period);
    $dir = new DirectoryIterator(pathinfo($pathfilename, PATHINFO_DIRNAME));
    debug('move file from', $pathfilename);
    debug('move file to  ', $year . ' / ' . $per_type . ' / ' . $period );
    foreach ($dir as $fileinfo) {
        // $fn = $fileinfo->getFileName();
        $fn = $fileinfo->getPathName();
        if (!string_starts_with($fn, file_change_extension($pathfilename, ''))) {
            continue;
        }
        $dfnp = filenameparts($fn);
        $dst = $destination . strtolower(substr($per_type, 0, 1)) . '_' . leading_zeros($period, 2) . '_' . $dfnp['username'] . '.' . $dfnp['extension'];
        // debug('Destination filename', $dst);
        if (!move_file($fn, $dst, FALSE)) {
            log_debug('Error moving file ', $fn . ' -> ' . $dst);
            $rc = FALSE;
        }
    }
    clearstatcache();
    return $rc;
}


// function deletes a set of files for one picture
// $pathfilname - .jpg picture name viewed from admin view like ../images/2022/W/01/w_01_alexander.xxx
function delete_picture_set($pathfilename)
{
    if (!check_filepath($pathfilename)) {
        return FALSE;
    }
    $rc = TRUE;
    // debug('Enter delete_pictur_set with', $pathfilename);
    $dir = new DirectoryIterator(pathinfo($pathfilename, PATHINFO_DIRNAME));
    foreach ($dir as $fileinfo) {
        $fn = $fileinfo->getPathName();
        if (!string_starts_with($fn, file_change_extension($pathfilename, ''))) {
            continue;
        }
        if (!unlink($fn)) {
            // debug('Error deleting file ', $pathfilename );
            $rc = FALSE;
        } else {
            // debug('Succsess deleting file ', $pathfilename );
        }
    }
    clearstatcache();
    return $rc;
}

// delete comment file
function delete_comment($pathfilename)
{
    if (!check_filepath($pathfilename)) {
        return FALSE;
    }
    return unlink(file_change_extension($pathfilename, 'txt'));
}

// change the file type (extension) of filename
function file_change_extension(string $file, string $new_ext)
{
    return substr($file, 0, strrpos($file, '.') + 1) . $new_ext;
}

// remove the file type (extension) of filename
function file_remove_extension(string $file)
{
    return substr($file, 0, strrpos($file, '.'));
}

// check filename path for correct directory
function check_filepath(string $pathfilename): bool
{
    // TODO check "realpath" as well!
    return string_starts_with($pathfilename, '../images/');
}

// drop starting prefix path name
function reduce_path($pathfilename)
{
    return substr($pathfilename, 10);
}

// deconstruct a WeeklyPic filename
// returns FALSE or a map with the following keys: path, filename, extension, basename, pertype, period, username, year, pathstate
// pathstate is TRUE if path matches the year, periode type, periode conventions, otherwise FALSE
function filenameparts($pathfilename)
{
    $parts = array();
    $n = strrpos($pathfilename, '/');
    if ($n === FALSE) {
        $parts['path'] = ''; // no path
        $parts['filename'] = $pathfilename;
    } else {
        $p = substr($pathfilename, 0, $n);
        $parts['path'] = $p;
        $parts['filename'] = substr($pathfilename, $n + 1);
    }
    // check validity
    $fn = $parts['filename'];
    if (substr($fn, 0, 1) <> 'm' && substr($fn, 0, 1) <> 'w') {
        return FALSE;
    }
    if (substr($fn, 1, 1) <> '_' || substr($fn, 4, 1) <> '_') {
        return FALSE;
    }
    if (strspn($fn, '0123456789', 2, 2) <> 2) {
        return FALSE;
    }
    // deconstruct filename
    $n = strrpos($fn, '.');
    if ($n === FALSE) {
        $parts['extension'] = ''; // no extension
        $parts['basename'] = $fn;
    } else {
        $parts['extension'] = substr($fn, $n + 1);
        $parts['basename'] = substr($fn, 0, $n);
    }
    $parts['pertype'] = strtoupper(substr($fn, 0, 1));
    $parts['period'] = substr($fn, 2, 2);
    $parts['username'] = substr($parts['basename'], 5);
    // debug('parts b', $parts);
    if( ! $n === FALSE ) { // we have a path
        $m = strrpos($p, '/');
        if($m === FALSE) {
            $parts['pathstate'] = FALSE;
            goto endfnparts;
        }
        $y = substr($p, $m + 1); // check if last part part matches period
        // $parts['yp'] = $y;
        if( $y != $parts['period'] )
        {
            $parts['pathstate'] = FALSE;
            goto endfnparts;
        }
        $x = substr($p, 0, $m); // reduced path
        // $parts['x2'] = $x;
        $m = strrpos($x, '/');
          if($m === FALSE) {
            $parts['pathstate'] = FALSE;
            goto endfnparts;
        }
        $y = substr($x, $m + 1); // check if last part part matches pertype
        // $parts['ypt'] = $y;
        if( $y != $parts['pertype'] )
        {
            $parts['pathstate'] = FALSE;
            goto endfnparts;
        }
        $x = substr($x, 0, $m); // reduced path
        // $parts['x3'] = $x;
        $m = strrpos($x, '/');
        if($m === FALSE) {
            $parts['pathstate'] = FALSE;
            goto endfnparts;
        }
        $parts['year'] = substr($x, $m + 1);
        $parts['pathstate'] = TRUE;
    }
    endfnparts:
    return $parts;
}

// return a hash with the existing image directories
// returns a hash of [year][M|W][period]="directory"
function image_dirs( ) {
    // read directorys in image directories
    $dir = new DirectoryIterator('../images');
    $dirs = array();
    foreach ($dir as $fileinfo) {
        // if it is directory loop it
        if ($fileinfo->isDir()) {
            $d = $fileinfo->getBasename();
        }
    }


}

?>