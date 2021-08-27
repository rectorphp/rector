<?php

namespace RectorPrefix20210827;

if (\class_exists('Tx_Fluid_View_Exception_InvalidTemplateResourceException')) {
    return;
}
class Tx_Fluid_View_Exception_InvalidTemplateResourceException
{
}
\class_alias('Tx_Fluid_View_Exception_InvalidTemplateResourceException', 'Tx_Fluid_View_Exception_InvalidTemplateResourceException', \false);
