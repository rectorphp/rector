<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

require __DIR__ . '/../src/tracy.php';
use RectorPrefix20210510\Tracy\Debugger;
use RectorPrefix20210510\Tracy\Dumper;
// For security reasons, Tracy is visible only on localhost.
// You may force Tracy to run in development mode by passing the Debugger::DEVELOPMENT instead of Debugger::DETECT.
Debugger::enable(Debugger::DETECT, __DIR__ . '/log');
?>
<!DOCTYPE html><link rel="stylesheet" href="assets/style.css">

<h1>Tracy: Dumper with common snapshot demo</h1>

<div itemscope>
<?php 
class Test
{
    public $x = [];
    protected $z = 30;
    private $y = 'hello';
}
\class_alias('RectorPrefix20210510\\Test', 'Test', \false);
$arr = [10, 'hello', \fopen(__FILE__, 'r')];
$obj = new Test();
$snapshot = [];
echo Dumper::toHtml($arr, [Dumper::SNAPSHOT => &$snapshot]);
echo Dumper::toHtml($obj, [Dumper::SNAPSHOT => &$snapshot]);
// changed array is detected
$arr[0] = 'CHANGED!';
echo Dumper::toHtml($arr, [Dumper::SNAPSHOT => &$snapshot]);
// changed object is not detected, because is part of snapshot
$obj->x = 'CHANGED!';
echo Dumper::toHtml($obj, [Dumper::SNAPSHOT => &$snapshot]);
// prints snapshot
echo '<meta itemprop=tracy-snapshot content=', Dumper::formatSnapshotAttribute($snapshot), '>';
echo '</div>';
if (Debugger::$productionMode) {
    echo '<p><b>For security reasons, Tracy is visible only on localhost. Look into the source code to see how to enable Tracy.</b></p>';
}
