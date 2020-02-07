<?php

function simplifyForeachToCoalescing2()
{
    foreach ($this->oldToNewFunctions as $oldFunction => $newFunction) {
        if ($oldFunction === $currentFunction) {
            return $newFunction;
        }
    }

    return 45;
}

?>
-----
<?php

function simplifyForeachToCoalescing2()
{
    return $this->oldToNewFunctions[$currentFunction] ?? 45;
}

?>
