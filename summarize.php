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

$summary = array();

$rfh = fopen($tester->reportsFile, 'r');

function uncsv($v) {
    if (strlen($v) and $v[0] === '"') {
        return str_replace('""', '"', substr($v, 1, -1));
    }
    return $v;
}

function firstword($v) {
    $words = explode(' ', $v);
    return $words[0];
}

$columns = null;
while ($line = fgets($rfh)) {
    if (!$columns) {
        $columns = explode(',', trim($line));
        continue;
    }

    $row = array();
    $inquote = false;
    $compile = "";
    $i=0;
    foreach (explode(',', trim($line)) as $v) {
        //throw out invalid rows
        if (!isset($columns[$i])) {
            continue 2;
        }
        $qstart = ($v && $v[0]==='"');
        $qend = (substr($v, -1) === '"' && substr($v, -2, 1) !== '"');
        if (!$inquote) {
            if (!($qstart xor $qend)) {
                $row[$columns[$i++]] = uncsv($v);
            } else {
                $compile = $v;
                $inquote = true;
            }
        } else {
            $compile .= ",$v";
            if ($qend) {
                $row[$columns[$i++]] = uncsv($compile);
                $inquote = false;
            }
        }
    }

    $key = null;
    switch($row['Platform']) {
        case 'iOS':
            $key = "Mobile Safari";
            break;

        case 'Android':
            $key = "{$row['Platform']} {$row['Browser']}";
            break;
        
        default:
            $key = $row['Browser'];
            break;
    }
    
    switch ($row['Browser']) {
        case 'Facebook': case 'Twitter': case 'Weibo':
            continue 2; //these will always be some version of an real browser
            
        case 'Unknown':
            continue 2; //not much we can do about this
    }
    
    if (!isset($summary[$key])) {
        foreach ($tester->tests as $rule => $info) {
            if (!isset($row[$rule])) continue;
            $status = firstword($row[$rule]);
            $summary[$key][$rule] = array(
                'latestReport' => $row['Report date'],
                'latestVersion' => $row['Version'],
                'currentSupport' => $status,
            );
            switch ($status) {
                case 'pass':
                    $summary[$key][$rule]['firstPass'] = $row['Version'];
                    break;
                case 'partial':
                    $summary[$key][$rule]['firstPartial'] = $row['Version'];
                    break;
            }
        }
    }

    foreach ($tester->tests as $rule => $info) {
        if (!isset($row[$rule])) continue;
        $status = firstword($row[$rule]);
        $summary[$key][$rule]['latestReport'] = max($summary[$key][$rule]['latestReport'], $row['Report date']);
        $summary[$key][$rule]['latestVersion'] = max($row['Version'], $summary[$key][$rule]['latestVersion']);
        switch ($status) {
            case 'pass':
                if ($row['Version']) {
                    $summary[$key][$rule]['firstPass'] = isset($summary[$key][$rule]['firstPass']) ? min($summary[$key][$rule]['firstPass'], $row['Version']) : $row['Version'];
                }
                $summary[$key][$rule]['currentSupport'] = 'pass';
                break;
            case 'partial':
                if ($row['Version']) {
                    $summary[$key][$rule]['firstPartial'] = isset($summary[$key][$rule]['firstPartial']) ? min($summary[$key][$rule]['firstPartial'], $row['Version']) : $row['Version'];
                }
                if ($summary[$key][$rule]['currentSupport'] === 'fail') {
                    $summary[$key][$rule]['currentSupport'] = 'partial';
                }
                break;
            case 'fail':
                $summary[$key][$rule]['firstFail'] = isset($summary[$key][$rule]['firstFail']) ? min($summary[$key][$rule]['firstFail'], $row['Version']) : $row['Version'];
                break;
        }
    }
}

header("Content-type: application/json; charset=utf-8");
print json_encode($summary, JSON_PRETTY_PRINT);
