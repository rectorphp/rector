<?php

namespace RectorPrefix20210827;

if (\interface_exists('Tx_Extbase_Persistence_RepositoryInterface')) {
    return;
}
interface Tx_Extbase_Persistence_RepositoryInterface
{
}
\class_alias('Tx_Extbase_Persistence_RepositoryInterface', 'Tx_Extbase_Persistence_RepositoryInterface', \false);
