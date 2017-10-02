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
    } else {
        $row = array();
        $inquote = false;
        $compile = "";
        $i=0;
        foreach (explode(',', trim($line)) as $v) {
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
    
    if (!isset($summary[$key])) {
        foreach ($tester->tests as $rule => $info) {
            if (!isset($row[$rule])) continue;
            $status = firstword($row[$rule]);
            $summary[$key][$rule] = array(
                'latestReport' => $row['Report date'],
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
        switch ($status) {
            case 'pass':
                $summary[$key][$rule]['firstPass'] = min($summary[$key][$rule]['firstPass'], $row['Version']);
                $summary[$key][$rule]['currentSupport'] = 'pass';
                break;
            case 'partial':
                $summary[$key][$rule]['firstPartial'] = min($summary[$key][$rule]['firstPartial'], $row['Version']);
                if ($summary[$key][$rule]['currentSupport'] !== 'pass') {
                    $summary[$key][$rule]['currentSupport'] = 'partial';
                }
                break;
        }
    }
}

var_dump($summary);