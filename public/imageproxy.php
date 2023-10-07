<?php
/**
 * Created by PhpStorm.
 * User: rene
 * Date: 14-09-15
 * Time: 21:00
 */

require_once __DIR__ . '/include/classImage.php';


function readHeader($resURL, $strHeader)
{
    header($strHeader);
    return strlen($strHeader);
}


$idArr = explode("/", $_GET['id']);
$img = new Image((int)$idArr[0], true);
$restrictPath = $img->isRestricted() ? '/cache/600x0-3' : '';

$url = "http://static.vici.org" . $restrictPath . $img->getPath();


//echo $url;
//exit;

$timeout = 10;
$referer = 'http://www.vici.org';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_HEADER, 0);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 0);
curl_setopt($ch, CURLOPT_HEADERFUNCTION, 'readHeader');

curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
curl_setopt($ch, CURLOPT_REFERER, $referer);

$data = curl_exec($ch);
curl_close($ch);
echo $data;