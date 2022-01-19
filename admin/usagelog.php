<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>WeeklyPic-One-Stop-Foto Admin - Usage-Log</title>
    <style type="text/css">
        body {
            margin: 5% auto;
            line-height: 1.6;
            font-size: 18px;
            color: #444;
            padding: 0 10px;
            background: #eee;
            -webkit-font-smoothing: antialiased;
            font-family: "Helvetica Neue", Helvetica, Arial, Verdana, sans-serif
        }

        h1, h2, h3 {
            line-height: 1.2
        }

        input {
            font-size: 18px
        }

        pre {
            border: 1px solid;
            padding: 0.5em;
            border-color: black
        }
    </style>
</head>
<body>

<h1>Anzeige des Nutzungs-Logs</h1>

<?php
// configuration constants
// include '../src/config.php';
// TODO: use config file
$usage_log = "../_log/usage.log";
setlocale(LC_CTYPE, 'en_US.UTF-8');
// more functions
include '../src/functions.php';

// check for file
if (file_exists($usage_log) == FALSE) {
    cancel_processing("Missing usage_log file.");
}

$nl = intval($_REQUEST['lines']);
$sel0150 = $sel0300 = $sel0600 = $sel1500 = $sel3000 = $sel010K = '';
switch ($nl) {
    case 150:
        $sel0150 = ' selected';
        break;
    case 600:
        $sel0600 = ' selected';
        break;
    case 1500:
        $sel1500 = ' selected';
        break;
    case 3000:
        $sel3000 = ' selected';
        break;
    case 10000:
        $sel010K = ' selected';
        break;
    default:
        $sel0300 = ' selected';
        $nl = 300;
        break;
}
$logic = $_REQUEST['logic'];
if ($logic == 'or') {
    $logic_and = '';
    $logic_or = ' checked';
} else {
    $logic = 'and';
    $logic_and = ' checked';
    $logic_or = '';
}
$filter1 = $_REQUEST['filter1'];
$filter2 = $_REQUEST['filter2'];
if ($filter1 == '' && $filter2 <> '') {
    $filter1 = $filter2;
    $filter2 = '';
}
// TODO: implement search from beginning of log (does not work because stripos() results in this case with 0, what also is FALSE)
if (string_starts_with($filter1, '202')) {
    $filter1 = substr($filter1, 1);
}
if (string_starts_with($filter2, '202')) {
    $filter2 = substr($filter2, 1);
}
?>

<form action="usagelog.php" method="post" enctype="multipart/form-data">
    <table>
        <tr>
            <td>Anzahl Zeilen: &nbsp;</td>
            <td>
                <select name="lines">
                    <option value="150" <?= $sel0150 ?>>150</option>
                    <option value="300" <?= $sel0300 ?>>300</option>
                    <option value="600" <?= $sel0600 ?>>600</option>
                    <option value="1500" <?= $sel1500 ?>>1.500</option>
                    <option value="3000" <?= $sel3000 ?>>3.000</option>
                    <option value="10000" <?= $sel010K ?>>10.000</option>
                </select>
                vom Ende des Protokolls
            </td>
        </tr>
        <tr>
            <td>Filter:</td>
            <td><input type="text" id="filter1" name="filter1" value="<?= $filter1 ?>"></td>
        </tr>
        <tr>
            <td></td>
            <td>
                <input type="radio" name="logic" id="logic_and" value="and" <?= $logic_and ?> ><label for="logic_and">
                    und </label>
                &nbsp;&nbsp;&nbsp;
                <input type="radio" name="logic" id="logic_or" value="or" <?= $logic_or ?> ><label for="logic_or">
                    oder </label>
            </td>
        <tr>
        <tr>
            <td></td>
            <td><input type="text" id="filter2" name="filter2" value="<?= $filter2 ?>"></td>
        </tr>
        <tr>
            <td><input type="submit" value="aktualisieren"></td>
            <td></td>
        </tr>
    </table>
</form>

<?PHP
// read usage_log
exec('tail -n ' . $nl . ' ' . $usage_log, $lines, $result);
//echo '<p>Es werden die letzten ' . $nl . ' Zeilen gefiltert. (Ergebnis von "tail": ' . $result . ' &nbsp; - &nbsp; 0=OK)</p>'
?>

<pre><?PHP
    foreach ($lines as $line) {
        if ($filter1 <> '') {
            if ($logic == 'and') {
                // TODO: implement search from beginning of log (does not work because stripos() results in this case with 0, what also is FALSE)
                if ((stripos($line, $filter1) <> FALSE) and (($filter2 == '') or (stripos($line, $filter2) <> FALSE))) {
                    echo htmlspecialchars($line) . "\n";
                }
            } else {
                if ((stripos($line, $filter1) <> FALSE) or (stripos($line, $filter2) <> FALSE)) {
                    echo htmlspecialchars($line) . "\n";
                }
            }
        } else {
            echo htmlspecialchars($line) . "\n";
        }
    }
    ?>
</pre>

<p>Das Format der Log-Datei ist wie folgt:</p>
<ul>
    <li>Eine Zeile besteht aus mehreren Feldern, welche durch ein ";" getrennt sind.</li>
    <li>Das erste Feld ist ein Zeitstempel. (In lokaler Zeit mit der Abweichung zu UTC.)</li>
    <li>Das zweite Feld besteht aus zwei Zeichen:
        <br/>Das erste Zeichen ist eine Ziffer von 1 bis 3 für die in der Reihenfolge üblicherweise
        aufgerufenen Seiten (1=Startseite=index.php, 2=Bearbeitungsseite=doit.php, 3=Fertig-Seite=final.php).
        Sollte dort ein "-" stehen kommt der Eintrag aus einer Funktion welche von verschiedenen Seiten aufgerufen
        werden konnte.
        <br/>Das zweite Zeichen ist entweder eine "E" für einen Fehler (Error), ein "A" für einen Abbruch in der
        Verarbeitung, ein "W" für eine Warnung, oder eine "I" für eine Information.
        <br/>Warnungen sind die nicht erfolgreichen Bildprüfungen, die nur den Upload in das "check" Verzeichnis
        erlauben.
    </li>
    <li>Das dritte Feld ist - sofern gefüllt - der User-Name. (Die Erfassung des Names kann abgeschaltet sein.)</li>
    <li>Das vierte Feld enthält weitere Informationen.</li>
</ul>

<br/>
<p>Hier geht es <a href="index.php">zurück</a>.</p>
<p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im
    WeeklyPic-Slack-Channel #entwickler-talk.</p>

</body>
</html>
