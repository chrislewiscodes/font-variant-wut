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

$summary = null;
if (false and file_exists('summary.json')) {
    $summary = json_decode(file_get_contents('summary.json'), true);
} else {
    $summary = json_decode(`php summarize.php`, true);
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <title>CSS font-variant tester</title>
    <link rel="stylesheet" href="fonts.css">
    <link rel="stylesheet" href="font-variant.css">
    <style>
        <?= $tester->atFontFace() ?>
        td.pass {
            background-color: #0C0;
        }
        
        td.partial {
            background-color: #FC0;
        }
        
        td.fail {
            background-color: #C00;
        }
    </style>
</head>

<body>
    <table id='summary'>
        <thead>
            <tr id='browsers'>
                <td></td>
            <?php foreach ($summary as $browser => $results): ?>
                <th class='browser'><?= $browser ?></th>
            <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tester->tests as $rule => $ruleinfo): ?>
            <tr>
                <th class='rule'><?= $rule ?></th>
                <?php foreach ($summary as $browser => $results): ?>
                <?php if (isset($results[$rule])): ?>
                <td class="<?= $results[$rule]["currentSupport"] ?>">
                    <?php if ($results[$rule]["currentSupport"]==='fail') {
                        print floor($results[$rule]["latestVersion"]);
                    } else if (!empty($results[$rule]["first" . ucfirst($results[$rule]["currentSupport"])])) {
                        print floor($results[$rule]["first" . ucfirst($results[$rule]["currentSupport"])]);
                    } ?>
                <?php else: ?>
                <td class="unknown"></td>
                <?php endif; ?>
                <?php endforeach; ?>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</body>

</html>