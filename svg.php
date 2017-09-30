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

$tester->useBlockFont();

$square = isset($_GET['px']) && is_numeric($_GET['px']) ? (int)$_GET['px'] : 10;

$totaltests = 0;
$cols = 0;
foreach ($tester->tests as $rule => $info) {
    $totaltests += count($info['values']) + 1; //initial
    $cols = max($cols, count($tester->featuresForRule($rule)));
}

$totalwidth = $square * ($cols + 2);
$totalheight = $square * $totaltests;

function texts($rule, $value, $y) {
    global $tester, $square;
    $x = 0;
    foreach ($tester->featuresForRule($rule) as $feat) {
        $x += $square;
        print "<text x='$x' y='$y' style='$rule:$value'>$feat</text>";
    }
    return "";
}

header("Content-type: image/svg+xml");
?>
<svg xmlns="http://www.w3.org/2000/svg" version="1.1" width="<?= $totalwidth ?>" height="<?= $totalheight ?>">
    <style>
        <?= $tester->atFontFace(true) ?>

        <?php $i=0; foreach ($tester->tests as $rule => $info): ?>
        .<?= $rule ?> rect.initial {
            stroke: <?= $tester->ruleColor($rule) ?>;
            fill: none;
        }
        
        .<?= $rule ?> text.label {
            fill: <?= $tester->ruleColor($rule) ?>;
            stroke: none;
        }
        <?php endforeach; ?>

        text {
            font-family: "Font Variant Test";
            font-size: <?= $square ?>px;
            text-rendering: optimizeLegibility;
            -ms-font-feature-settings "liga" 1;
        }
        
        text.label {
            font-family: sans-serif;
            font-size: <?= $square * 0.8 ?>px;
            text-anchor: end;
            transform: translateX(-<?= $square*0.2 ?>px);
        }
    </style>

    <rect x="0" y="0" width="<?= $totalwidth ?>" height="<?= $totalheight ?>" fill="#ffffff" stroke="none"/>
    <?php $i=0; foreach ($tester->tests as $rule => $info): ?>
        <g class="<?= $rule ?>">
            <rect class="initial <?= $rule ?>" x="0.5" y="<?= $i * $square+0.5 ?>" width="<?= $totalwidth-1 ?>" height="<?= $square-1 ?>"/> 
            <text class="label <?= $rule ?>" x="<?= $totalwidth ?>" y="<?= ($i+0.75) * $square ?>"><?= str_replace('font-variant-', '', $rule) ?></text>
            <?= texts($rule, 'initial', ($i+1)*$square) ?>
            <?php ++$i; foreach ($info['values'] as $value): ?>
            <?= texts($rule, $value, ($i+1)*$square) ?>
            <?php ++$i; endforeach; ?>
        </g>
    <?php endforeach; ?>

</svg>
