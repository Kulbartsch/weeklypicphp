#! /bin/bash

# wepistat_fs.sh: read data from filesystem and genertate a statistc.
# Copyright © 2022 Alexander Kulbartsch 
# License: AGPL-3.0-or-later (GNU Affero General Public License 3 or later)

if test $# -eq 1; then
  jahr=$1
else
  jahr=$(date +"%Y");
fi

echo Verarbeite Jahr ${jahr}

# fetch data and insert in CSV

echo "Jahr;MW;P;Name" > wepistat_${jahr}.csv

find ../images/${jahr} -name "*.jpg" -printf "%f\n" | awk -v year=${jahr} 'BEGIN{ FS="_"; OFS=";"} { print year, $1, $2, substr($0,6,length($0)-9); }' >> wepistat_${jahr}.csv

# generate statistics -> total.txt, monat.txt, woche.txt, user.txt
echo generiere einzelne Statistiken ...
awk -f wepistat.awk wepistat_${jahr}.csv

# generate text-output with ASCII charts
#echo generiere Text-Ausgabe ...
#./textoutput.sh ${jahr} > wepistat_${jahr}.txt

# generate HTML-output 
echo generiere HTML-Ausgabe ...
./htmloutput.sh ${jahr} > wepistat_${jahr}.html

echo Fertig.
# EOF
