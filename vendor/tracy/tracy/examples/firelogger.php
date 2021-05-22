<?php

declare (strict_types=1);
namespace RectorPrefix20210522;

require __DIR__ . '/../src/tracy.php';
use RectorPrefix20210522\Tracy\Debugger;
$arr = [10, 20, ['key1' => 'val1', 'key2' => \true]];
// will show in FireLogger
\RectorPrefix20210522\Tracy\Debugger::fireLog('Hello World');
\RectorPrefix20210522\Tracy\Debugger::fireLog($arr);
function first($arg1, $arg2)
{
    \RectorPrefix20210522\second(\true, \false);
}
function second($arg1, $arg2)
{
    \RectorPrefix20210522\third([1, 2, 3]);
}
function third($arg1)
{
    throw new \Exception('The my exception', 123);
}
try {
    \RectorPrefix20210522\first(10, 'any string');
} catch (\Exception $e) {
    \RectorPrefix20210522\Tracy\Debugger::fireLog($e);
}
?>
<!DOCTYPE html><link rel="stylesheet" href="assets/style.css">

<h1>Tracy: FireLogger demo</h1>

<p>How to <a href="https://tracy.nette.org/#toc-firelogger">enable FireLogger</a>?</p>
<?php 
