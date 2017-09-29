<?php

// what features we got
preg_match_all('/\bfeature\s+(\w{4})\s*\{/', file_get_contents('fonts/OTTestFont-Regular.ufo/features.fea'), $m, PREG_PATTERN_ORDER);

$allfeatures = $m[1];
sort($allfeatures);

$tests = array(
    "font-variant-alternates" => array(
        'values' => array('normal', 'historical-forms', 'stylistic(1)', 'styleset(1)', 'character-variant(1)', 'swash(1)', 'ornaments(1)', 'annotation(1)'),
        'features' => '.alt|ss\d\d|cv\d\d|swsh|cswh|hist|ornm',
    ),
    "font-variant-caps" => array(
        'values' => array('normal', 'small-caps', 'all-small-caps', 'petite-caps', 'all-petite-caps', 'unicase', 'titling-caps'),
        'features' => 'c2pc|c2sc|pcap|smcp|unic|titl',
    ),
    "font-variant-ligatures" => array(
        'values' => array('none', 'common-ligatures', 'discretionary-ligatures', 'historical-ligatures', 'contextual'),
        'features' => '.lig|liga',
    ),
    "font-variant-numeric" => array(
        'values' => array('normal', 'lining-nums', 'oldstyle-nums', 'proportional-nums', 'tabular-nums', 'diagonal-fractions', 'stacked-fractions', 'ordinal', 'slashed-zero'),
        'features' => '.num|frac|afrc|zero|ordn',
    ),
    "font-variant-position" => array(
        'values' => array('normal', 'sub', 'super'),
        'features' => 'subs|sups',
    ),
);

function testString($features) {
    global $allfeatures;
    if (is_array($features)) {
        return implode("\n", $features);
    } else {
        $result = array();
        foreach ($allfeatures as $f) {
            if (preg_match('/' . $features . '/', $f)) {
                $result[] = $f;
            }
        }
        return implode("\n", $result);
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>CSS font-variant tester</title>
    <link href="https://fonts.googleapis.com/css?family=Source+Code+Pro:500,700|Source+Sans+Pro:400,900" rel="stylesheet">
    <style>
        @font-face {
            src: url("fonts/test.woff");
            font-family: "Font Variant Test";
            font-weight: normal;
            font-style: normal;
        }
        html {
            font-family: "Source Sans Pro", sans-serif;
        }
        code, tt {
            font-family: "Source Code Pro", monospace;
            font-weight: 500;
        }
        h1 {
            font-weight: 900;
        }
        h1 code {
            font-weight: 700;
        }
        
        table {
            border-collapse: collapse;
        }
        
        th, td {
            border: 1px solid #999;
        }
        
        tbody th {
            font-family: "Source Code Pro", monospace;
            font-weight: 500;
            text-align: left;
        }
        
        tbody th.rule {
            vertical-align: middle;
            font-weight: bold;
        }
        
        tbody th.value {
            vertical-align: bottom;
        }
        
        td {
            vertical-align: top;
        }
        
        td.test {
            white-space: pre;
        }
        
        .test {
            font-family: "Font Variant Test";
            vertical-align: top;
            word-break: break-all;
        }
    </style>
</head>
    
<body>

    <h1>CSS <code>font-variant</code> tester</h1>

    <!-- All features: <?= implode(" ", $allfeatures) ?> -->

    <table>
        <tbody>
        <?php foreach ($tests as $rule => $info): ?>
            <tr>
                <th rowspan='2' class='rule'><?= $rule ?></th>
                <?php foreach ($info['values'] as $value): ?>
                <th class='value'><?= $value ?></th>
                <?php endforeach; ?>
            </tr>
            <tr>
                <?php foreach ($info['values'] as $value): ?>
                <td class='test' style='<?= $rule ?>:<?= $value ?>'><?= testString($info['features']) ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</body>    
    

</html>