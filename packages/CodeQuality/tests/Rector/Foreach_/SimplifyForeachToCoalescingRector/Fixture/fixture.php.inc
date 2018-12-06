<?php

function simplifyForeachToCoalescing()
{
    foreach ($this->oldToNewFunctions as $oldFunction => $newFunction) {
        if ($currentFunction === $oldFunction) {
            return $newFunction;
        }
    }

    return 45;

    foreach ($this->oldToNewFunctions as $oldFunction => $newFunction) {
        if ($currentFunction === $oldFunction) {
            return 15;
        }
    }
    return 0;
}

?>
-----
<?php

function simplifyForeachToCoalescing()
{
    return $this->oldToNewFunctions[$currentFunction] ?? 45;
    foreach ($this->oldToNewFunctions as $oldFunction => $newFunction) {
        if ($currentFunction === $oldFunction) {
            return 15;
        }
    }
    return 0;
}

?>
