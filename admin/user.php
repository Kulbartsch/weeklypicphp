<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WeeklyPic-One-Stop-Foto</title>
    <style type="text/css">
      body {margin:30px auto; max-width:650px; line-height:1.6; font-size:18px;
        color:#444; padding:0 10px;
        background:#eee;
        -webkit-font-smoothing:antialiased;
        font-family:"Helvetica Neue",Helvetica,Arial,Verdana,sans-serif }
      h1,h2,h3 {line-height:1.2}
      input {font-size:18px}
      pre {border:1px solid; padding:0.5em; border-color:black}
      textarea {border:1px solid; padding:0.5em; border-color:black}
    </style>
  </head>
  <body>

    <h1>Bearbeiten der Benutzer-Datei</h1>
    
    <p>Das Format der Datei ist wie folgt:</p>
    <ul>
      <li>Leere Zeilen werden ignoriert.</li>
      <li>Zeilen, welche mit einem # beginnen sind Kommentarzeilen und werden ignoriert.</li>
      <li>Alle anderen Zeilen beschreiben je Zeile einen Benutzer.</li>
      <li>Eine Benutzer-Zeile kann aus mehreren Feldern bestehen, welche durch ein ; getrennt sind.</li>
      <li>Das erste Feld ist der Slack-Name und muss angegeben werden. Die folgenden Felder sind optional.</li>
      <li>Das zweite Feld ist der Name mit dem der Benuzer in der App angeredet wird. Ist dieser nicht angegeben wird der Slack-Name verwendet.</li>
      <li>Alle weiteren Felder werden ignoriert.</li>
    </ul>
    <p>Beispiel:<br />
    <pre>Benutzername;Ansprache;weitere Felder werden ignoriert
Johann_B;Johann Sebastian;j.bach@example.com
Marie;;Marie.Curie@example.com</pre></p>

    <h3>Die Benutzer-Datei:</h3>
    <?php
      // configuration constants
      // include '../src/config.php';
      $user_file = "../_log/user.txt";
      setlocale(LC_CTYPE, 'en_US.UTF-8');
      // more functions
      include '../src/functions.php';
      // check for file
      if(file_exists($user_file) == FALSE){
        cancel_processing("Missing user file.");
      }
      // read file
      $user_lines = explode(PHP_EOL, file_get_contents($user_file));
    ?>

    <form action="user_save.php" id="myform" method="post" enctype="multipart/form-data">

<textarea type="text" form="myform" id="users" name="users" required rows="25" cols="60" maxlenght="50000">
<?PHP foreach ($user_lines as $line) { echo htmlspecialchars($line) . "\n"; } ?>
</textarea><br />

      <input type="submit" value="speichern" name="submit">

    </form>

    <p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im WeeklyPic-Slack-Channel #entwickler-talk.</p>

  </body>
</html>
