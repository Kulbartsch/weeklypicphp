# 

cat << EOF
<!DOCTYPE html>
<html lang="de">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>WeeklyPic Statistik</title>
  <style>
    body {margin: 5px;
          color: #444;
          background-color: #eee;
          font-family: Helvetica,Arial,Verdana,sans-serif; }
  </style>
<style>
/* include css within <head> tags as
   <link rel="stylesheet" href="<filename>.css">
 */
:root {
    --bars_col_background: #CCC;       /* light grey   */
    --bars_col_positive:   #FF00FF;    /* magenta      */
    --bars_col_negative:   #008B8B;    /* cyan         */
    --bars_col_zero:       #333;       /* dark grey 1  */
    --bars_col_header:     #444;       /* dark grey 2  */
    --bars_col_label:      #008;       /* dark blue    */
    --bars_col_value:      #050;       /* dark green   */
    --bars_col_footer:     #555;       /* dark grey 3  */
}
.bars_chart {
    display: grid;
    grid-template-columns: repeat(205, 1fr);
    grid-row-gap: 5px;
    font-family: Helvetica,Arial,Verdana,sans-serif;
    background-color: var(--bars_col_background);
    border: 2px solid;
    border-radius: 3px;
    padding: 4px;
    white-space: nowrap;
}
.bars_header {
    border-bottom: 2px solid;
    font-weight: bold;
    color: var(--bars_col_header);
}
.bars_label {
    grid-column-start: 1;
    grid-column-end: 2;
    color: var(--bars_col_label);
}
.bars_value {
    grid-column-start: 3;
    grid-column-end: 4;
    color: var(--bars_col_value);
    text-align: right;
}
.bars_neg {
    border-radius: 5px 0 0 5px;
    background-color: var(--bars_col_negative);
}
.bars_zero {
    background-color: var(--bars_col_zero);
}
.bars_pos {
    border-radius: 0 5px 5px 0;
    background-color: var(--bars_col_positive);
}

.bars_footer1 {
    border-top: 2px solid;
}
.bars_footer_left {
    font-style: italic;
    grid-column: 1/2;
    color: var(--bars_col_footer);
}
.bars_footer_mid {
    font-style: italic;
    grid-column: 3/4;
    text-align: right;
    color: var(--bars_col_footer);
}
.bars_footer_right {
    font-style: italic;
    grid-column: 5/206;
    color: var(--bars_col_footer);
}

</style>
</head>
<body>
EOF

datum=`date +"%Y-%m-%d %H:%M:%S %Z"`

echo "<h1>WeeklyPic Statistik für ${1}</h1>"
echo "<p>Stand: ${datum}</p>"


./bars -mode=snippet \
     -title="Bilder im Jahr" \
     -label-header=Typ \
     -value-header=Anzahl \
     -sum \
     total.txt
     
./bars -mode=snippet \
     -title="Bilder pro Monat" \
     -label-header=Monat \
     -value-header=Anzahl \
     -average-text="Durchschnittliche Anzahl Bilder pro Monat" \
     -sum -average \
     monat.txt
     
./bars -mode=snippet \
     -title="Bilder pro Woche" \
     -label-header=KW \
     -value-header=Anzahl \
     -average-text="Durchschnittliche Anzahl Bilder pro Woche" \
     -sum -average \
     woche.txt
     
sort -nr user.txt | ./bars -mode=snippet \
     -title="Bilder pro Teilnehmer" \
     -label-header=Teilnehmer \
     -value-header=Anzahl \
     -average-text="Durchschnittliche Anzahl Bilder pro Teilnehmer" \
     -sum -average \
     -count -count-text="Anzahl Teilnehmer"
     
echo "<p>Hier geht es <a href="index.php">zurück</a>.</p>"
echo "</body></html>"
