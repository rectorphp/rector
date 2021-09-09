<?php

namespace RectorPrefix20210909;

if (\class_exists('Tx_Fluid_Exception')) {
    return;
}
class Tx_Fluid_Exception
{
}
\class_alias('Tx_Fluid_Exception', 'Tx_Fluid_Exception', \false);
