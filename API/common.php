<?php

function realPathForParams($type, $year, $number)
{
    if ($type === "week") {
        return realpath("./../images/" . $year . "/w/" . $number . "/");
    } else if ($type === "month") {
        return realpath("./../images/" . $year . "/m/" . monthInGerman($number) . "/");
    } else {
        echo($type);
        throw new Exception("Invalid type");
    }
}

function monthInGerman($month)
{
    $time = strtotime("1-" . $month . "-2000");
    $format = datefmt_create('de_DE', IntlDateFormatter::NONE, IntlDateFormatter::NONE, NULL, NULL, "MMMM");
    $localizedMonth = datefmt_format($format, $time);
    $localizedMonth = str_replace("ä", "ae", $localizedMonth);
    return strtolower($localizedMonth);
}
