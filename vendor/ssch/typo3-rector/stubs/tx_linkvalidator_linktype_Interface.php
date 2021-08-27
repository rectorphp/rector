<?php

namespace RectorPrefix20210827;

if (\interface_exists('tx_linkvalidator_linktype_Interface')) {
    return;
}
interface tx_linkvalidator_linktype_Interface
{
}
\class_alias('tx_linkvalidator_linktype_Interface', 'tx_linkvalidator_linktype_Interface', \false);
