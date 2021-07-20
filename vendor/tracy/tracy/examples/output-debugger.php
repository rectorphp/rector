<?php

declare (strict_types=1);
namespace RectorPrefix20210720;

require __DIR__ . '/../src/tracy.php';
\RectorPrefix20210720\Tracy\OutputDebugger::enable();
function head()
{
    echo '<!DOCTYPE html><link rel="stylesheet" href="assets/style.css">';
}
\RectorPrefix20210720\head();
echo '<h1>Output Debugger demo</h1>';
