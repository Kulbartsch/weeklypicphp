<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>WeeklyPic-One-Stop-Foto Admin - Bilder prüfen</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>

<h1>Bilder prüfen</h1>

<?php
include '../src/functions.php';
include '../src/filestore.php';

echo '<table><tr class="list"><th>Bild-Datei</th><th>Kommentar</th><th span=3>Aktion</th></tr>' . PHP_EOL;
$fstc = find_files_to_check();
foreach ($fstc as $ftc) {
    $jpgfile = file_change_extension($ftc[0], 'jpg');
    $year_null = ( substr($jpgfile, 10, 1) == '0' );
    echo '<tr class="list"><td><a href="' . $jpgfile . '">' . substr($jpgfile, 10) . '</a></td>';
    echo '<td>' . $ftc[1] . '</td>';
    echo '<td>'; 
    if( $year_null ) {
        echo '<a href="file_move1.php?accept=TRUE&file=' . $ftc[0] . '" class="btn">akzeptieren&nbsp;und&nbsp;verschieben</a>';
    } else {
        echo '<a href="file_accept.php?file=' . $ftc[0] . '" class="btn">akzeptieren</a>';
        echo '&nbsp;&nbsp;<a href="file_move1.php?accept=TRUE&file=' . $ftc[0] . '" class="btn">...&nbsp;und&nbsp;verschieben</a>';
    }
    echo '&nbsp;&nbsp;<a href="files_delete.php?file=' . $ftc[0] . '" class="btn">löschen</a>';
    echo '</td></tr>' . PHP_EOL;
}
echo '</table>';
?>

<br/>
<p>Hier geht es <a href="index.php" class="btn">zurück</a>.</p>
<p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im
    WeeklyPic-Slack-Channel #entwickler-talk.</p>

</body>
</html>
