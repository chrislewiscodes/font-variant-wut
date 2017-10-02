<?php
namespace FontVariantWut;

/*
    Browser font-variant support tester by Chris Lewis https://chrislewis.codes/
    Brilliant test font by David Jonathan Ross https://djr.com/
    
    Licensed under the MIT License. Code available on GitHub:
    https://github.com/chrissam42/font-variant-wut
*/

require_once("./FontVariantWut.class.php");

$tester = new FontVariantWut();

function errrr($code, $msg) {
    header("HTTP/1.1 $code");
    die($msg);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    errrr(405, "POST required");
}

$json = file_get_contents("php://input");

if (!$json) {
    errrr(400, "Empty POST message");
}

$report = json_decode($json);

if (!is_object($report)) {
    errrr(400, "JSON required");
}

header("Content-type: text/plain");

$columns = array_merge(
    array('date', 'platform', 'browser', 'version'),
    array_keys($tester->tests),
    array('userAgent')
);

$rfh = null;
if (!file_exists($tester->reportsFile) || !filesize($tester->reportsFile)) {
    try {
        $rfh = fopen($tester->reportsFile, 'w');
        fwrite($rfh, implode(",", $columns) . "\r\n");
    } catch (\Exception $e) {
        errrr(400, "Error accessing result columns: $e");
    }
} else {
    $rfh = fopen($tester->reportsFile, 'a');
}

function csvCol($val, $end=false) {
    global $rfh;
    $comma = strpos($val, ',') !== false;
    $quote = strpos($val, '"') !== false;
    
    $insert = $val;
    if ($quote || $comma) {
        $insert = str_replace('"', '""', $val);
        $insert = '"' . $insert . '"';
    }
    $insert .= $end ? "\r\n" : ",";
    
    fwrite($rfh, $insert);
}

try {
    csvCol($report->when);
    csvCol($report->browser->platform);
    csvCol($report->browser->browser);
    csvCol($report->browser->version);
    foreach ($tester->tests as $rule => $info) {
        if (isset($report->results->$rule)) {
            $r = $report->results->$rule;
            csvCol($r->finalAnswer . ' ' . count($r->uniqueValues) . '/' . count($r->rowValues));
        }
    }
    csvCol($report->browser->userAgent, true);
} catch (\Exception $e) {
    fwrite($rfh, $e . "\r\n");
    fclose($rfh);
    errrr(400, "Error writing row: $e");
}

fclose($rfh);

print "Thanks!";
