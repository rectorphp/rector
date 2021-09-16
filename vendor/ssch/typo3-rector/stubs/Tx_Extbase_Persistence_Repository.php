<?php

namespace RectorPrefix20210916;

if (\class_exists('Tx_Extbase_Persistence_Repository')) {
    return;
}
class Tx_Extbase_Persistence_Repository
{
}
\class_alias('Tx_Extbase_Persistence_Repository', 'Tx_Extbase_Persistence_Repository', \false);
