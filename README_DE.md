# weeklypicphp

Ein (einfaches) PHP-Skript, um ein JPG-Bild auf die richtigen Proportionen zu skalieren, EXIF-Daten für unsere Weeklypic.de-Community zu setzen und das Bild hochzuladen.

# Features

* Bild auf 2000px skalieren (längste Seite)
* Maximale Bildgröße auf 500KB setzen
* Metadaten-Daten einstellen für
  * *Titel*
  * *Beschreibung* (= weekliy-pic-username / title)
  * *Urheber*
  * *Lizenz*
* Tabelle der vorhandenen/erforderlichen/neuen EXIF-Daten anzeigen
* Speicherung der Parameter *Weekly-Pic-Benutzername*, *Urheber*, *Lizenz* und *GPS-Daten-löschen* in einem Cookie, für deinen Komfort, falls gewünscht.
* Wahlmöglichkeit zwischen *wöchentlich* und *monatlich*
* Voreinstellung der Wochen- und Monatsnummern für den Dateinamen (änderbar)
* Automatische Generierung eines gültigen Weekly-Pic Dateinamens
* Direkter Upload des Bildes an weeklypic.de
* Mobilgeräte freundliches Design
* Aufruf einer Karte mit den GPS Koordinaten des Bildes (sofern vorhanden)
* Prüfen des Aufnahmedatums gegen die Woche, bzw. den Monat
* Teilnehmer prüfen
* Optional den Titel aus den Metadaten heranziehen
* Problembehaftete Bilder, in ein "zu prüfen" Verzeichnis hochladen
* Keine Anpassung der Bildgröße, wenn diese schon OK ist
* Expertenmodus, in dem keine Metadaten geändert werden
* Anzeige aller Metadaten eines Bildes

# Anwender Dokumentation

Siehe ["how to use.pdf"](how to use.pdf) (deutsch).

# Roadmap

* Korrektur falscher EXIF-Daten aus dem Export von Darktable (siehe "Bekannte Probleme")

# Hinweis

Es sind nicht nur EXIF-Tags in Bildern gespeichert, sondern auch IPTC, GPS und andere Tags.
Wann immer wir EXIF sagen/schreiben, meinen wir im Allgemeinen auch alle anderen Tags.

So sind beispielsweise, wenn der Künstler geändert werden soll, die folgenden Tags betroffen:
* EXIF:Artist
* IPTC:by-line
* XMP:creator

# Bekannte Probleme

* Aus Darktable exportierte EXIF-Daten wahrscheinlich falsch.
  * Es sieht so aus, dass in MacOS aus Darktable exportierte Bilder bei der Verarbeitung mit exiftool zu einem `Error = Bad Format (0) für IFD0-Eintrag 0` führen. Das Bild wird dann nicht angepasst und die Verarbeitung bricht ab. Ich arbeite an einer Lösung, dass in diesem und ähnlichen Fällen die Metadaten neu, richtig geschrieben werden.

## behoben 

* Es kann fälschlicherweise angezeigt werden, dass GPS Daten vorhanden sind, obwohl diese gelöscht wurden, weil noch eine GPS-Version-ID vorhanden ist. Geodaten wurden allerdings gelöscht. 
