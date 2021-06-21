<?php

declare (strict_types=1);
namespace RectorPrefix20210621;

require __DIR__ . '/../src/tracy.php';
use RectorPrefix20210621\Tracy\Debugger;
use RectorPrefix20210621\Tracy\Dumper;
// For security reasons, Tracy is visible only on localhost.
// You may force Tracy to run in development mode by passing the Debugger::DEVELOPMENT instead of Debugger::DETECT.
\RectorPrefix20210621\Tracy\Debugger::enable(\RectorPrefix20210621\Tracy\Debugger::DETECT, __DIR__ . '/log');
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
\class_alias('RectorPrefix20210621\\Test', 'Test', \false);
$arr = [10, 'hello', \fopen(__FILE__, 'r')];
$obj = new \RectorPrefix20210621\Test();
$snapshot = [];
echo \RectorPrefix20210621\Tracy\Dumper::toHtml($arr, [\RectorPrefix20210621\Tracy\Dumper::SNAPSHOT => &$snapshot]);
echo \RectorPrefix20210621\Tracy\Dumper::toHtml($obj, [\RectorPrefix20210621\Tracy\Dumper::SNAPSHOT => &$snapshot]);
// changed array is detected
$arr[0] = 'CHANGED!';
echo \RectorPrefix20210621\Tracy\Dumper::toHtml($arr, [\RectorPrefix20210621\Tracy\Dumper::SNAPSHOT => &$snapshot]);
// changed object is not detected, because is part of snapshot
$obj->x = 'CHANGED!';
echo \RectorPrefix20210621\Tracy\Dumper::toHtml($obj, [\RectorPrefix20210621\Tracy\Dumper::SNAPSHOT => &$snapshot]);
// prints snapshot
echo '<meta itemprop=tracy-snapshot content=', \RectorPrefix20210621\Tracy\Dumper::formatSnapshotAttribute($snapshot), '>';
echo '</div>';
if (\RectorPrefix20210621\Tracy\Debugger::$productionMode) {
    echo '<p><b>For security reasons, Tracy is visible only on localhost. Look into the source code to see how to enable Tracy.</b></p>';
}
