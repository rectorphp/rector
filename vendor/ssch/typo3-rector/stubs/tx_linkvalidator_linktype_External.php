<?php

namespace RectorPrefix20210809;

if (\class_exists('tx_linkvalidator_linktype_External')) {
    return;
}
class tx_linkvalidator_linktype_External
{
}
\class_alias('tx_linkvalidator_linktype_External', 'tx_linkvalidator_linktype_External', \false);
