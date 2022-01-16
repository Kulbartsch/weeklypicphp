<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WeeklyPic-One-Stop-Foto Admin - Bild verschieben</title>
    <style type="text/css">
      body {margin:5% auto; line-height:1.6; font-size:18px;
        color:#444; padding:0 10px;
        background:#eee;
        -webkit-font-smoothing:antialiased;
        font-family:"Helvetica Neue",Helvetica,Arial,Verdana,sans-serif }
      h1,h2,h3 {line-height:1.2}
      input {font-size:18px}
      pre {border:1px solid; padding:0.5em; border-color:black}
      table {border: 1px;}
      th {text-align: left;}
    </style>
  </head>
  <body>

      
    <?php
        include '../src/functions.php';
        include '../src/filestore.php';

        $accept = $_REQUEST['accept'];
        $file = file_change_extension($_REQUEST['file'], 'jpg');
        
        if( $accept=='TRUE') {
            echo '<h1>Bild akzeptieren und verschieben</h1>'; 
        } else {
            echo '<h1>Bild verschieben</h1>'; 
        }

        echo '<p> Datei ' . reduce_path($file);

    ?>
    
    <h3>Bild verschieben zu</h3>

    <form action="file_move2.php" method="post" enctype="multipart/form-data">
    
     <input type="text" id="file" name="file" value="<?= $file ?>" required hidden>
     <input type="text" id="accept" name="accept" value="<?= $accept ?>" hidden>
     <!-- TODO: dynamic year range -->
     <p><label for="year">Jahr: </label><input type="number" id="year" name="year" min="2022" max="2023" step="1=" value="2022"><br/></p>
     <p>Bild-Zeitraum:
        <table>
        <tr><td><input type="radio" id="timeframe_w" name="timeframe" value="Woche" checked required>
          <label for="timeframe_w">Woche</label></td>
          <td><input type="number" id="week_number" name="week_number" min="1" max="53" step="1=" value="1"></td></tr>  <!-- TODO: use current week -->
        <tr><td><input type="radio" id="timeframe_m" name="timeframe" value="Monat" required>
          <label for="timeframe_m">Monat</label></td>
          <td><input type="number" id="month_number" name="month_number" min="1" max="12" step="1=" value="1"></td></tr> <!-- TODO: use current month --> 
        </table>
    </p>
    <p></p>
      <input type="submit" value="Bild verschieben" name="submit">
    </p>
    </form>

    <br />
    <p>Hier geht es <a href="index.php">zurück</a>.</p>
    <p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im WeeklyPic-Slack-Channel #entwickler-talk.</p>

  </body>
</html>
