<?php

namespace RectorPrefix20210902;

if (\class_exists('Tx_Extbase_Persistence_Repository')) {
    return;
}
class Tx_Extbase_Persistence_Repository
{
}
\class_alias('Tx_Extbase_Persistence_Repository', 'Tx_Extbase_Persistence_Repository', \false);
