
# wepistat_2.awk: generate different statistic files
# Copyright © 2021 Alexander Kulbartsch 
# License: AGPL-3.0-or-later (GNU Affero General Public License 3 or later)
 
BEGIN { FS=";" }

# 2021;m;01;andreas

/^20.*/ { 

# Total - Anzahl Monats +  Wochenbilder
  total[$2]++

  if ($2 == "w")
#   wochen - anzahl bilder / woche 
    woche[$3]++;
  else
#   monat - anzahl bilder / monat 
    monat[$3]++;

# user - anzahl bilder / person
  user[$4]++;   
    
}

END {

	for (i in total) {
	    if (i == "w") label = "Woche"; else label = "Monat";
		print total[i], label > "total.txt";
	}

	#for (i in woche) {
	#	print woche[i], i > "woche.txt";
	#}
    n=asorti(woche, sorted)
    for (i=1; i<=n; i++)
        print woche[sorted[i]], sorted[i]  > "woche.txt";

	n=asorti(monat, sorted)
    for (i=1; i<=n; i++)
        print monat[sorted[i]], sorted[i]  > "monat.txt";

    n=asorti(user, sorted)
    for (i=1; i<=n; i++)
        print user[sorted[i]], sorted[i]  > "user.txt";

}
