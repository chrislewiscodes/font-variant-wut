<?php
namespace FontVariantWut;

require_once("./FontVariantWut.class.php");

$tester = new FontVariantWut();

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

            function fontAvailable() {
                return context.measureText("a").width === 0;
            }

            var interval;
            interval = setInterval(function() {
                if (!fontAvailable()) return;
                clearInterval(interval);

                var img = document.createElement('img');
                img.addEventListener('load', function() {
                    canvas.width = this.naturalWidth;
                    canvas.height = this.naturalHeight;
                    context.drawImage(this, 0, 0);
                    document.body.appendChild(canvas);
                });
                img.src = "test-svg.php";
                document.body.appendChild(img);

            }, 100);
            
        })();
    </script>
</head>
    
<body>

    <h1>CSS <code>font-variant</code> tester</h1>

    <p id='browser'>Your browser: <strong><?= $tester->getBrowser() ?></strong></p>

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
                <td class='test' style='<?= $rule ?>:initial' data-rule='<?= $rule ?>' data-value='initial'><?= $tester->getTestString($info['features']) ?></td>
                <?php foreach ($info['values'] as $value): ?>
                <td class='test' style='<?= $rule ?>:<?= $value ?>' data-rule='<?= $rule ?>' data-value='<?= $value ?>'><?= $tester->getTestString($info['features']) ?></td>
                <?php endforeach; ?>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>

</body>

</html>