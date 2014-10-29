<?php

header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");

$titleArray = array();
$linkArray = array();
$artistArray = array();
$result = array();
$testArray = '[" - ","title1 - artist1"," - ","title2 - artist2","","link1","","link2"]';
$url = "";
$decode = 99;
$which = 99;
$totalCnt = 0;

if (!empty($_POST['ALBUM'])) {
    parse_str($_POST['ALBUM']);
    clearAllArrays();
    requestXMLData($loc, $code, 1);
}
if (!empty($_POST['ALBUMS'])) {
    parse_str($_POST['ALBUMS'], $rcvArray);
    $idx = 0;
    $indexedVal = array_values($rcvArray);  // index control
    clearAllArrays();
    for ($idx = 0; $idx < count($indexedVal) - 1; $idx++) { // -1, last one is code, not used
        requestXMLData($indexedVal[$idx], $indexedVal[count($indexedVal) - 1], count($indexedVal) - 1);
    }
}

function getResult() {
    global $titleArray;
    global $linkArray;
    global $artistArray;
    global $result;

    for ($r = 0; $r < count($titleArray); $r++) {
        if ((strlen($titleArray[$r]) > 0) && (strlen($artistArray[$r]) > 0)) {
            $result[] = $titleArray[$r] . " - " . $artistArray[$r];
        }
    }
    for ($r = 0; $r < count($linkArray); $r++) {
        if (strlen($linkArray[$r]) > 0) {
            $result[] = $linkArray[$r];
        }
    }

    echo die(json_encode($result));
    return ($result);
}

function getTitle() {
    global $titleArray;
    return ($titleArray);
}

function getLink() {
    global $linkArray;
    return ($linkArray);
}

function getArtist() {
    global $artistArray;
    return ($artistArray);
}

function clearAllArrays() {
    global $titleArray;
    global $linkArray;
    global $artistArray;
    global $result;
    $titleArray = array();
    $linkArray = array();
    $artistArray = array();
    $result = array();
}

function requestXMLData($url, $decode, $count) {
    global $result;
    global $titleArray;
    global $linkArray;
    global $artistArray;
    global $testArray;
    global $totalCnt;
    $tcnt = $lcnt = $acnt = 0;
    $parser = xml_parser_create();

    xml_set_element_handler($parser, "start", "stop");
    xml_set_character_data_handler($parser, "char");

    if (strcmp($decode, "1") == 0) {
        $data = trim(gzdecode(file_get_contents($url))); // Z single album
    } else
        $data = '<?xml version="1.0" encoding="UTF-8"?>' . trim(file_get_contents($url)); // Z radio and NCTui album

    xml_parse($parser, $data, null) or 
            die(sprintf("XML Error: %s at line %d", xml_error_string(xml_get_error_code($parser)), xml_get_current_line_number($parser)));
    xml_parser_free($parser);

    $totalCnt++;
    if ($totalCnt == $count) {
        getResult();
    }
}

function start($parser, $element_name, $element_attrs) {
    global $which;
    switch ($element_name) {
        case "CREATOR":
            $which = 2;
            // echo '<input type="text" name="artist" value="';
            break;
        case "LOCATION":
            $which = 1;
            // echo '<input type="text" name="source" value="';
            break;
        case "TITLE":
            $which = 0;
            // echo '<input type="text" name="title" value="';
            break;
        case "PERFORMER":
            $which = 2;
            // echo '<input type="text" name="artist" value="';
            break;
        case "SOURCE":
            $which = 1;
            // echo '<input type="text" name="source" value="';
            break;
        default:
            $which = 4;
        //echo '<input type="text" name="none" value="';
    }
}

function stop($parser, $element_name) {
    //echo '"><br>';
    global $tcnt;
    global $lcnt;
    global $acnt;
    global $which;
    switch ($which) {
        case 0:
            $tcnt++;
            break;
        case 1:
            $lcnt++;
            break;
        case 2:
            $acnt++;
            break;
        default: break;
    }
}

function char($parser, $data) {
    global $titleArray;
    global $linkArray;
    global $artistArray;
    global $which;
    switch ($which) {
        case 0:
            $titleArray[] = (string) trim($data);
            // echo trim($data) . "\n";
            break;
        case 1:
            $linkArray[] = (string) trim($data);
            // echo trim($data) . "\n";
            break;
        case 2:
            $artistArray[] = (string) trim($data);
            // echo trim($data) . "\n";
            break;
        default: break;
    }
    //   echo trim($data);
}
?>

