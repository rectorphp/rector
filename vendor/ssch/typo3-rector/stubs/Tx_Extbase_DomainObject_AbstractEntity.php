<?php

namespace RectorPrefix20211003;

if (\class_exists('Tx_Extbase_DomainObject_AbstractEntity')) {
    return;
}
class Tx_Extbase_DomainObject_AbstractEntity
{
}
\class_alias('Tx_Extbase_DomainObject_AbstractEntity', 'Tx_Extbase_DomainObject_AbstractEntity', \false);
