<?php

function realPathForParams($type, $year, $number)
{
    if ($type === "week") {
        return realpath("./../images/" . $year . "/W/" . $number . "/");
    } else if ($type === "month") {
        //return realpath("./../images/" . $year . "/M/" . monthInGerman($number) . "/");
        return realpath("./../images/" . $year . "/M/" . $number . "/");
    } else {
        echo($type);
        throw new Exception("Invalid type");
    }
}

function monthInGerman($month): string
{
    // $time = strtotime("1-" . $month . "-2000");
    // $format = datefmt_create('de_DE', IntlDateFormatter::NONE, IntlDateFormatter::NONE, NULL, NULL, "MMMM");
    // $localizedMonth = datefmt_format($format, $time);
    // $localizedMonth = str_replace("ä", "ae", $localizedMonth);
    // return strtolower($localizedMonth);

      switch ($month) {
        case 1:
          return 'januar';
          break;
        case 2:
          return 'februar';
          break;
        case 3:
          return 'maerz';
          break;
        case 4:
          return 'april';
          break;
        case 5:
          return 'mai';
          break;
        case 6:
          return 'juni';
          break;
        case 7:
          return 'juli';
          break;
        case 8:
          return 'august';
          break;
        case 9:
          return 'september';
          break;
        case 10:
          return 'oktober';
          break;
        case 11:
          return 'november';
          break;
        case 12:
          return 'dezember';
          break;
        default:
          return 'unbekannt';
      }
    
}
