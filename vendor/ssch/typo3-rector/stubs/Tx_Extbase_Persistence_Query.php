<?php

namespace RectorPrefix20210909;

if (\class_exists('Tx_Extbase_Persistence_Query')) {
    return;
}
class Tx_Extbase_Persistence_Query
{
}
\class_alias('Tx_Extbase_Persistence_Query', 'Tx_Extbase_Persistence_Query', \false);
