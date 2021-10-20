<?php

namespace RectorPrefix20211020;

if (\interface_exists('Tx_Fluid_Core_ViewHelper_ViewHelperInterface')) {
    return;
}
interface Tx_Fluid_Core_ViewHelper_ViewHelperInterface
{
}
\class_alias('Tx_Fluid_Core_ViewHelper_ViewHelperInterface', 'Tx_Fluid_Core_ViewHelper_ViewHelperInterface', \false);
