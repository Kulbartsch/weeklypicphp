<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WeeklyPic-One-Stop-Foto Admin - Dev Tests</title>
    <link rel="stylesheet" href="admin.css">
</head>
<body>
    <h1>Entwicklungs-Testseite</h1>

    <?php
    setlocale(LC_CTYPE, 'en_US.UTF-8');
    include '../src/filestore.php';

    // Get Parameters
    $p1 = isset($_POST['p1']) ? $_POST['p1'] : '';

    // Form 
    ?>

    <form action="test.php" method="post">
        <label for="p1">Parameter 1: </label><input type="text" name="p1" id="p1" value="<?= $p1 ?>"><br><br>
        <input type="submit" name="filenameparts" value="Filename-Parts">
    </form>
    <br>

    <?php

    // immediate test
    echo '<h2>Debug Info</h2>';
    // echo "<p>number_format: ", number_format(1234.567, 2, ',', '.'), '</p>';
    echo '<p>p1: ', $p1, '</p>';


    echo '<h2>Ergebnis</h2>';

    // Processing tests
    echo '<p>Test: ';
    if(isset($_POST['filenameparts'])) {
        echo 'Filename-Parts';
        $r1 = filenameparts($p1);
    } else {
        echo '(Kein Test angefordert/durchgeführt)';
        $r1 = '';
    }
    echo '</p>', PHP_EOL;

    // Display result
    echo '<p>Ergebnis</p>', PHP_EOL;
    echo '<pre>', PHP_EOL;
    echo var_dump($r1);
    // foreach ($lines as $line) {
    //     echo htmlspecialchars($line) . "\n";
    // }
    echo '</pre>', PHP_EOL;

    ?>

    <br />
    <p>Hier geht es <a href="index.php">zurück</a>.</p>
    <p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im
        WeeklyPic-Slack-Channel #entwickler-talk.</p>

</body>

</html>