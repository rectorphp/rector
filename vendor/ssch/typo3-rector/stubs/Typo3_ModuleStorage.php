<?php

namespace RectorPrefix20211002;

if (\class_exists('Typo3_ModuleStorage')) {
    return;
}
class Typo3_ModuleStorage
{
}
\class_alias('Typo3_ModuleStorage', 'Typo3_ModuleStorage', \false);
