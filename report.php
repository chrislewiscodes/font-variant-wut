<?php
namespace FontVariantWut;

/*
    Browser font-variant support tester by Chris Lewis https://chrislewis.codes/
    Brilliant test font by David Jonathan Ross https://djr.com/
    
    Licensed under the MIT License. Code available on GitHub:
    https://github.com/chrissam42/font-variant-wut
*/

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

$knownrules = array(
    'font-variant-alternates',
    'font-variant-caps',
    'font-variant-ligatures',
    'font-variant-numeric',
    'font-variant-position',
);

$columns = array_merge(
    array('Report date', 'Platform', 'Browser', 'Version'),
    $knownrules,
    array('User agent')
);

$reportsfile = "reports.csv";
$rfh = null;
if (!file_exists($reportsfile) || !filesize($reportsfile)) {
    try {
        $rfh = fopen($reportsfile, 'w');
        fwrite($rfh, implode(",", $columns) . "\r\n");
    } catch (\Exception $e) {
        errrr(400, "Error accessing result columns: $e");
    }
} else {
    $rfh = fopen($reportsfile, 'a');
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
    foreach ($knownrules as $rule) {
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
