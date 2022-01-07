
echo "*** WeeklyPic Statistik für ${1} ***"
echo
echo Stand: `date +"%Y-%m-%d %H:%M:%S %Z"`
echo

bars -mode=plain \
     -title="Bilder im Jahr" \
     -label-header=Typ \
     -value-header=Anzahl \
     -sum \
     total.txt
     
echo
echo

bars -mode=plain \
     -title="Bilder pro Monat" \
     -label-header=Monat \
     -value-header=Anzahl \
     -average-text="Durchschnittliche Anzahl Bilder pro Monat" \
     -sum -average \
     monat.txt
     
echo 
echo 

bars -mode=plain \
     -title="Bilder pro Woche" \
     -label-header=KW \
     -value-header=Anzahl \
     -average-text="Durchschnittliche Anzahl Bilder pro Woche" \
     -sum -average \
     woche.txt
     
echo 
echo 

sort -nr user.txt | bars -mode=plain \
     -title="Bilder pro Teilnehmer" \
     -label-header=Teilnehmer \
     -value-header=Anzahl \
     -average-text="Durchschnittliche Anzahl Bilder pro Teilnehmer" \
     -sum -average \
     -count -count-text="Anzahl Teilnehmer"
     
echo
echo "** ** **" 
