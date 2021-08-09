<?php

namespace RectorPrefix20210809;

if (\class_exists('Tx_Extbase_Persistence_Query')) {
    return;
}
class Tx_Extbase_Persistence_Query
{
}
\class_alias('Tx_Extbase_Persistence_Query', 'Tx_Extbase_Persistence_Query', \false);
