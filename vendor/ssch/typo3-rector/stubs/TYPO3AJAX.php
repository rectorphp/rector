<?php

namespace RectorPrefix20210913;

if (\class_exists('TYPO3AJAX')) {
    return;
}
class TYPO3AJAX
{
}
\class_alias('TYPO3AJAX', 'TYPO3AJAX', \false);
