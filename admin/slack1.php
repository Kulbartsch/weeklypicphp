<!DOCTYPE html>
<html lang="de">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>WeeklyPic-One-Stop-Foto - Slack Nachricht</title>
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

    <h1>Senden einer Slack Nachricht</h1>
    
    <p>Die Funktion ist noch in der <b>Testphase!</b><p>

    <form action="slack2.php" id="myform" method="post" enctype="multipart/form-data">

      @Benutzername oder #Channel:<br/>
      <input type="text" id="to" name="to" value="" required><br/>
      Nachricht:<br/>
      <input type="text" id="message" name="message">

      <input type="submit" value="senden" name="submit">

    </form>

    <p>Hier geht es <a href="index.php">zurück ohne zu senden!</a>.</p>
    <p>Sollte etwas nicht wie erwartet funktionieren, informiere bitte den Admin dieses Servers, bzw. im WeeklyPic-Slack-Channel #entwickler-talk.</p>

  </body>
</html>
