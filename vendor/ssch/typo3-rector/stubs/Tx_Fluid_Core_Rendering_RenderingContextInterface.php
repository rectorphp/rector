<?php

namespace RectorPrefix20210827;

if (\interface_exists('Tx_Fluid_Core_Rendering_RenderingContextInterface')) {
    return;
}
interface Tx_Fluid_Core_Rendering_RenderingContextInterface
{
}
\class_alias('Tx_Fluid_Core_Rendering_RenderingContextInterface', 'Tx_Fluid_Core_Rendering_RenderingContextInterface', \false);
