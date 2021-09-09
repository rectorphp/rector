<?php

namespace RectorPrefix20210909;

if (\class_exists('Tx_Extbase_Persistence_Generic_Exception_InconsistentQuerySettings')) {
    return;
}
class Tx_Extbase_Persistence_Generic_Exception_InconsistentQuerySettings
{
}
\class_alias('Tx_Extbase_Persistence_Generic_Exception_InconsistentQuerySettings', 'Tx_Extbase_Persistence_Generic_Exception_InconsistentQuerySettings', \false);
