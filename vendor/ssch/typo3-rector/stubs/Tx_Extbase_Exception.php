<?php

namespace RectorPrefix20210707;

if (\class_exists('Tx_Extbase_Exception')) {
    return;
}
class Tx_Extbase_Exception
{
}
\class_alias('Tx_Extbase_Exception', 'Tx_Extbase_Exception', \false);
