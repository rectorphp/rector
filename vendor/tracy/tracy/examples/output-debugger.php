<?php

declare (strict_types=1);
namespace RectorPrefix20220102;

require __DIR__ . '/../src/tracy.php';
\RectorPrefix20220102\Tracy\OutputDebugger::enable();
function head()
{
    echo '<!DOCTYPE html><link rel="stylesheet" href="assets/style.css">';
}
\RectorPrefix20220102\head();
echo '<h1>Output Debugger demo</h1>';
