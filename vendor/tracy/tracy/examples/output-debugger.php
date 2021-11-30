<?php

declare (strict_types=1);
namespace RectorPrefix20211130;

require __DIR__ . '/../src/tracy.php';
\RectorPrefix20211130\Tracy\OutputDebugger::enable();
function head()
{
    echo '<!DOCTYPE html><link rel="stylesheet" href="assets/style.css">';
}
\RectorPrefix20211130\head();
echo '<h1>Output Debugger demo</h1>';
