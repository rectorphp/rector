<?php

declare (strict_types=1);
namespace RectorPrefix20211231;

require __DIR__ . '/../src/tracy.php';
use RectorPrefix20211231\Tracy\Debugger;
// For security reasons, Tracy is visible only on localhost.
// You may force Tracy to run in development mode by passing the Debugger::DEVELOPMENT instead of Debugger::DETECT.
\RectorPrefix20211231\Tracy\Debugger::enable(\RectorPrefix20211231\Tracy\Debugger::DETECT, __DIR__ . '/log');
\RectorPrefix20211231\Tracy\Debugger::$strictMode = \true;
?>
<!DOCTYPE html><link rel="stylesheet" href="assets/style.css">

<h1>Tracy Warning and StrictMode demo</h1>

<?php 
$f = \fopen('nonexistent', 'r');
if (\RectorPrefix20211231\Tracy\Debugger::$productionMode) {
    echo '<p><b>For security reasons, Tracy is visible only on localhost. Look into the source code to see how to enable Tracy.</b></p>';
}
