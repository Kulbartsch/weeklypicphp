<?php
include("common.php");

define('PUBLIC_PATH', '/var/www/html');

class ImageData {
    public $url = '';
    public $exif = '';
    public $iptc = '';
}

$request_method=$_SERVER["REQUEST_METHOD"];

$type=$_GET["type"]; // week OR month
$number=$_GET["number"]; // kw or month
$year=$_GET["year"];
$filename=$_GET["filename"];

switch($request_method)
{
    case 'GET':
		getMetaData($type, $year, $number, $filename);
		break;
	default:
		// Invalid Request Method
		header("HTTP/1.0 405 Method Not Allowed");
		break;
}
    
function getMetaData($type, $year, $number, $filename)
{
    $filePath = realPathForParams($type, $year, $number) . '/' . $filename;
    if (is_file($filePath)) {
        header('Content-Type: application/json');
        $exif = exif_read_data($filePath);
        $arrSize = getimagesize($filePath, $arrInfo);
        $arrIPTC = iptcparse($arrInfo['APP13']);
        $imgData = new ImageData();
        $imgData->url = path2url($filePath);
        if (isset($exif['ImageDescription'])) {
            $imgData->exif = mb_convert_encoding($exif['ImageDescription'], 'UTF-8', 'UTF-8');
        }
        if (is_array($arrIPTC) && isset($arrIPTC["2#120"][0])) {
            $imgData->iptc = mb_convert_encoding($arrIPTC['2#120'][0], 'UTF-8', 'UTF-8');
        }
		echo json_encode($imgData);
    } else {
        header("HTTP/1.0 404 Not Found");
        echo($filePath);
    }
}

function path2url($file, $Protocol='https://') {
    return $Protocol.$_SERVER['HTTP_HOST'].str_replace(PUBLIC_PATH, '', realpath($file));
}