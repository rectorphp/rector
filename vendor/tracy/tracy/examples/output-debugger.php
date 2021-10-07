<?php

declare (strict_types=1);
namespace RectorPrefix20211007;

require __DIR__ . '/../src/tracy.php';
\RectorPrefix20211007\Tracy\OutputDebugger::enable();
function head()
{
    echo '<!DOCTYPE html><link rel="stylesheet" href="assets/style.css">';
}
\RectorPrefix20211007\head();
echo '<h1>Output Debugger demo</h1>';
