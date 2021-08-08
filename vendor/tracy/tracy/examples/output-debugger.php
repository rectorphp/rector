<?php

declare (strict_types=1);
namespace RectorPrefix20210808;

require __DIR__ . '/../src/tracy.php';
\RectorPrefix20210808\Tracy\OutputDebugger::enable();
function head()
{
    echo '<!DOCTYPE html><link rel="stylesheet" href="assets/style.css">';
}
\RectorPrefix20210808\head();
echo '<h1>Output Debugger demo</h1>';
