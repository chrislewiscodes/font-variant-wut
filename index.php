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

function browser() {
    if (preg_match('/MSIE (\d+(?:\.\d+)?)/', $_SERVER['HTTP_USER_AGENT'], $m)) {
        return "Internet Explorer {$m[1]}";
    }
    if (preg_match('~(Firefox|Chrome|Chromium|Safari|OPR|Opera)/(\d+(?:\.\d+))~', $_SERVER['HTTP_USER_AGENT'], $m)) {
        if ($m[1] === 'OPR') {
            $m[1] = 'Opera';
        }
        return "{$m[1]} {$m[2]}";
    }
    return "Unknown";
}

$svgtestsize = 24;
$totaltests = 0;
foreach ($tests as $rule => $info) {
    $totaltests += count($info['values']) + 1; //initial
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>CSS font-variant tester</title>
    <script async src="snap.svg-min.js"></script>
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

        #browser {
            font-size: 1.5rem;
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
        
        .yes {
            background-color: #9f9;
        }
        
        .no {
            background-color: #f66;
        }
        
        svg {
            border: 1px solid black;
        }
    </style>
    <script>
        (function() {
            "use strict";
            
            // use canvas font measurement to see when font has loaded
            var canvas = document.createElement('canvas');
            var context = canvas.getContext('2d');
            context.font = '50px "Font Variant Test"';

            var svg;

            var interval;

            function fontAvailable() {
                return context.measureText("a").width === 0;
            }

            function testFeature(feature) {
                return false;
            }
            
            function testAllFeatures() {
                var i=0;
                document.querySelectorAll('td.test').forEach(function(cell) {
                    var rule = cell.getAttribute('data-rule');
                    var value = cell.getAttribute('data-value');
                    var testText = cell.textContent;

                    if (value === 'initial') {
                        var bg = svg.rect(0, i * <?= $svgtestsize ?>, svg.attr('width'), <?= $svgtestsize ?>);
                        bg.addClass('initial');
                        var label = svg.text(svg.attr('width')-10, (i+0.7) * <?= $svgtestsize ?>, rule.replace(/font-variant-/, ''));
                        label.addClass('label');
                    }
                    
                    var testEl = svg.text(10, (i + 0.7) * <?= $svgtestsize ?>, testText);
                    testEl.attr({'style': rule + ':' + value});
                    
                    ++i;
                });
                
/*
                console.log(svg.node);
                canvas.width = svg.attr('width');
                canvas.height = svg.attr('height');
                context.drawImage(0, 0, svg.node);
                document.body.appendChild(canvas);
*/
            }

            interval = setInterval(function() {
                if (!fontAvailable()) return;
                if (!window.Snap) return;
                clearInterval(interval);

                svg = Snap(document.querySelector('svg'));

                testAllFeatures();
            }, 100);
            
        })();
    </script>
</head>
    
<body>

    <h1>CSS <code>font-variant</code> tester</h1>

    <p id='browser'>Your browser: <strong><?= browser() ?></strong></p>

    <!-- All features: <?= implode(" ", $allfeatures) ?> -->

    <table>
        <tbody>
        <?php foreach ($tests as $rule => $info): ?>
                <th rowspan='2' class='rule' data-rule='<?= $rule ?>'><?= $rule ?></th>
                <th class='value'>initial</th>
                <?php foreach ($info['values'] as $value): ?>
                <th class='value'><?= $value ?></th>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td class='test' style='<?= $rule ?>:initial' data-rule='<?= $rule ?>' data-value='initial'><?= testString($info['features']) ?></td>
                <?php foreach ($info['values'] as $value): ?>
                <td class='test' style='<?= $rule ?>:<?= $value ?>' data-rule='<?= $rule ?>' data-value='<?= $value ?>'><?= testString($info['features']) ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="300" height="<?= ($svgtestsize*$totaltests) ?>">
        <style>
            text {
                font-family: "Font Variant Test";
                font-size: <?= $svgtestsize ?>;
            }
            
            text.label {
                font-family: "Source Code Pro", sans-serif;
                font-size: 16;
                text-anchor: end;
            }
            
            .initial {
                fill: #CCF;
            }
        </style>
    </svg>
</body>

</html>