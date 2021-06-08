<?php

namespace RectorPrefix20210608;

if (\interface_exists('tx_form_System_Postprocessor_Interface')) {
    return;
}
interface tx_form_System_Postprocessor_Interface
{
}
\class_alias('tx_form_System_Postprocessor_Interface', 'tx_form_System_Postprocessor_Interface', \false);
