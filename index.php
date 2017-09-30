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

$browser = $tester->browser();

$rulecolors = array();
foreach ($tester->tests as $rule => $info) {
    $rulecolors[$rule] = $tester->ruleColor($rule, 'rgb');
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>CSS font-variant tester</title>
    <link href="https://fonts.googleapis.com/css?family=Source+Code+Pro:500,700|Source+Sans+Pro:400,900" rel="stylesheet">
    <style>
        <?= $tester->atFontFace() ?>

        html {
            font-family: "Source Sans Pro", sans-serif;
        }

        code, tt {
            font-family: "Source Code Pro", monospace;
            font-weight: 500;
        }

        footer {
            
        }

        h1 {
            font-weight: 900;
        }

        h1 code {
            font-weight: 700;
        }

        p {
            max-width: 40em;
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
        
        td.test span {
            display: block;
            text-rendering: optimizeLegibility;
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
    </style>
    <script>
        (function() {
            "use strict";

            var ruleColors = <?= json_encode($rulecolors) ?>;
            
            // use canvas font measurement to see when font has loaded
            var canvas = document.createElement('canvas');
            var context = canvas.getContext('2d');
            context.font = '50px "Font Variant Test"';

            function fontAvailable() {
                return context.measureText("a").width === 0;
            }

/*
            var interval;
            interval = setInterval(function() {
                if (!fontAvailable()) return;
                clearInterval(interval);
            }, 100);
*/

            var square = 10;
            function analyzeImage() {
                var result = {};
                result.browser = <?= json_encode($browser); ?>;
                var w = canvas.width;
                var h = canvas.height;
                var imageData = context.getImageData(0, 0, w, h);
                var pixels = imageData.data;
                var x, y, i, r, g, b, on;
                var mid = Math.floor(square/2);
                var currentRule, isInitial;
                var px0, white=[255,255,255], black=[0,0,0];
                var rowValue;
                
                var results = <?= json_encode($tester->tests) ?>;

                function sameColor(px1, px2) {
                    var rd = px2[0]-px1[0];
                    var gd = px2[1]-px1[1];
                    var bd = px2[2]-px1[2];
                    var squaredist = rd*rd + gd*gd + bd*bd;
                    return squaredist < 100;
                }
                
                //pixels is a linear array of RGBA integers
                for (y=mid; y<h; y+=square) {
                    //sample leftmost pixel to see if we have a new rule
                    i = 4*y*w;
                    px0 = [pixels[i+0], pixels[i+1], pixels[i+2], pixels[i+3]];
                    if (!sameColor(px0, white)) {
                        Object.keys(ruleColors).forEach(function(rule) {
                            if (sameColor(ruleColors[rule], px0)) {
                                currentRule = rule;
                                isInitial = true;
                                if (!(rule in results)) {
                                    results[rule] = {};
                                }
                                results[rule].initialValue= 0;
                                results[rule].rowValues= [];
                                results[rule].uniqueValues= [];
                            }
                        });
                    }
                    
                    rowValue = 0;
                    for (x=mid; x<w; x+=square) {
                        i = 4 * (y*w + x);
                        r = pixels[i+0];
                        g = pixels[i+1];
                        b = pixels[i+2];

                        on = sameColor(black, [r,g,b]) ? 1 : 0;

                        if (isInitial) {
                            results[currentRule].initialValue = results[currentRule].initialValue * 2 + on;
                        } else {
                            rowValue = rowValue * 2 + on;
                        }
                    }
                    
                    if (!isInitial) {
                        if (results[currentRule].rowValues.indexOf(rowValue) < 0) {
                            results[currentRule].uniqueValues.push(rowValue);
                        }
                        results[currentRule].rowValues.push(rowValue);
                    }
                    
                    isInitial = false;
                }
                
                Object.keys(results).forEach(function(rule) {
                    var r = results[rule];
                    if (r.uniqueValues.length >= r.rowValues.length-1) {
                        r.finalAnswer = 'pass';
                    } else if (r.uniqueValues.length > 1) {
                        r.finalAnswer = 'partial';
                    } else {
                        r.finalAnswer = 'fail';
                    }
                });
                
                return results;
            }
            
            function reportResults(r) {
                var report = {};
                report.when = (new Date()).toISOString();
                report.results = r;
                report.browser = <?= json_encode($tester->browser()) ?>;
                console.log(report);
                var xhr = new XMLHttpRequest();
                xhr.open("POST", "report.php");
                xhr.setRequestHeader("Content-type", "application/json");
                xhr.onerror = function() { console.log('Error', xhr.status, xhr.statusText); };
                xhr.onload = function() { };
                xhr.send(JSON.stringify(report));
            }

            document.addEventListener('DOMContentLoaded', function() {
                var img = document.createElement('img');
                img.addEventListener('load', function() {
                    //takes a fraction of a second before the font is ready
                    setTimeout(function() {
                        canvas.width = img.naturalWidth;
                        canvas.height = img.naturalHeight;
                        context.drawImage(img, 0, 0);
                        //document.body.appendChild(canvas);
                        var results = analyzeImage();
                        document.body.removeChild(img);
                        reportResults(results);
                    }, 200);
                });
                img.style.position = 'absolute';
                img.style.left = '-200vw';
                img.style.top = '0';
                img.src = "svg.php?px=" + square;
                document.body.appendChild(img);
            });
        })();
    </script>
</head>
    
<body>

    <h1>CSS <code>font-variant</code> tester</h1>

    <p>This page assesses whether your browser supports the various
        <a href="https://drafts.csswg.org/css-fonts-3/#font-rend-props">font-variant-<i>…</i> rules</a> defined in CSS3.
        It uses a special test font, designed by <a href="https://djr.com/">David Jonathan Ross</a>, which uses
        OpenType features to display the name of the feature only when that feature is activated.
    </p>
    
    <p>The table below shows the various <em>font-variant-____</em> rules and their values, 
        and the related OpenType features (if any) that are activated by the browser in response to those rules.
    </p>
    
    <p>This page also records a report of your browser’s performance, 
        in order to build a comprehensive listing of browser support for these bleeding-edge rules.
        No personally-identifying information is recorded.
    </p>

    <p id='browser'>Your browser: <strong><?= $browser['browser'] ?> <?= $browser['version'] ?> (<?= $browser['platform'] ?>)</strong> <?= $_SERVER['HTTP_USER_AGENT'] ?></p>

    <!-- All features: <?= implode(" ", $tester->allfeatures) ?> -->

    <table>
        <tbody>
        <?php foreach ($tester->tests as $rule => $info): ?>
                <th rowspan='2' class='rule' data-rule='<?= $rule ?>'><?= $rule ?></th>
                <th class='value'>initial</th>
                <?php foreach ($info['values'] as $value): ?>
                <th class='value'><?= $value ?></th>
                <?php endforeach; ?>
            </tr>
            <tr>
                <td class='test' style='<?= $rule ?>:initial' data-rule='<?= $rule ?>' data-value='initial'><?= $tester->testString($rule) ?></td>
                <?php foreach ($info['values'] as $value): ?>
                <td class='test' style='<?= $rule ?>:<?= $value ?>' data-rule='<?= $rule ?>' data-value='<?= $value ?>'><?= $tester->testString($rule) ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

    <footer>
        Tool written by <a href="https://chrislewis.codes/">Chris Lewis</a>.
        Source code available on <a href="https://github.com/chrissam42/font-variant-wut">GitHub</a>.
    </footer>

</body>

</html>