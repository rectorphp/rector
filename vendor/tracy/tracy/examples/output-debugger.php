<?php

declare (strict_types=1);
namespace RectorPrefix20210510;

require __DIR__ . '/../src/tracy.php';
Tracy\OutputDebugger::enable();
function head()
{
    echo '<!DOCTYPE html><link rel="stylesheet" href="assets/style.css">';
}
head();
echo '<h1>Output Debugger demo</h1>';
