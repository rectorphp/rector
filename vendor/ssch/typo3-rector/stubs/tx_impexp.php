<?php

namespace RectorPrefix20211103;

if (\class_exists('tx_impexp')) {
    return;
}
class tx_impexp
{
}
\class_alias('tx_impexp', 'tx_impexp', \false);
