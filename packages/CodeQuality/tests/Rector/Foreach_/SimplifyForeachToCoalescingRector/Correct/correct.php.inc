<?php

return $this->oldToNewFunctions[$currentFunction] ?? 45;
foreach ($this->oldToNewFunctions as $oldFunction => $newFunction) {
    if ($currentFunction === $oldFunction) {
        return 15;
    }
}
return 0;