<?php

namespace RectorPrefix20210916;

if (\class_exists('Tx_Extbase_Exception')) {
    return;
}
class Tx_Extbase_Exception
{
}
\class_alias('Tx_Extbase_Exception', 'Tx_Extbase_Exception', \false);
