<?php

namespace RectorPrefix20210809;

if (\class_exists('Tx_Extbase_Persistence_QueryResult')) {
    return;
}
class Tx_Extbase_Persistence_QueryResult
{
}
\class_alias('Tx_Extbase_Persistence_QueryResult', 'Tx_Extbase_Persistence_QueryResult', \false);
