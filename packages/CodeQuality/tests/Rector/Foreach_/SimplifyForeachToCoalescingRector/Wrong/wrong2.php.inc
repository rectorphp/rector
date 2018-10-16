<?php

foreach ($this->oldToNewFunctions as $oldFunction => $newFunction) {
    if ($oldFunction === $currentFunction) {
        return $newFunction;
    }
}

return 45;
