<?php
include("common.php");
$request_method=$_SERVER["REQUEST_METHOD"];

$type=$_GET["type"]; // week OR month
$number=$_GET["number"]; // number of kw or month
$year=$_GET["year"];
switch($request_method)
{
    case 'GET':
		listDir($type, $year, $number);
		break;
	default:
		// Invalid Request Method
		header("HTTP/1.0 405 Method Not Allowed");
		break;
}
    
function listDir($type, $year, $number)
{
    $dirPath = realPathForParams($type, $year, $number);
    
    if (is_dir($dirPath)) {
        header('Content-Type: application/json');
        $dir = new DirectoryIterator($dirPath);
        $response=array();
        foreach ($dir as $fileinfo) {
            $fn = $fileinfo->getFileName();
            if($fileinfo->isDot() || 
               !$fileinfo->isFile() ||
               startsWith($fn, $year . "_w_") ||
			   startsWith($fn, $year . "_m_") ||
               !endsWith($fn, ".jpg") || 
               is_file(substr($dirPath . '/' . $fn, 0, -3) . 'txt')) {
                continue;
            }
            $response[]=$fn;
        }
		echo json_encode($response);
    } else {
        header("HTTP/1.0 404 Not Found");
        echo($dirPath);
    }
}

function startsWith($haystack, $needle)
{
    $length = strlen($needle);
    return (substr($haystack, 0, $length) === $needle);
}

function endsWith($haystack, $needle)
{
    $length = strlen($needle);
    if ($length == 0) {
        return true;
    }

    return (substr($haystack, -$length) === $needle);
}
