<?php

namespace RectorPrefix20210909;

if (\class_exists('Tx_Extbase_Persistence_QueryResult')) {
    return;
}
class Tx_Extbase_Persistence_QueryResult
{
}
\class_alias('Tx_Extbase_Persistence_QueryResult', 'Tx_Extbase_Persistence_QueryResult', \false);
