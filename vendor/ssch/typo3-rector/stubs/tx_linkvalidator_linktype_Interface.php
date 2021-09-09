<?php

namespace RectorPrefix20210909;

if (\interface_exists('tx_linkvalidator_linktype_Interface')) {
    return;
}
interface tx_linkvalidator_linktype_Interface
{
}
\class_alias('tx_linkvalidator_linktype_Interface', 'tx_linkvalidator_linktype_Interface', \false);
