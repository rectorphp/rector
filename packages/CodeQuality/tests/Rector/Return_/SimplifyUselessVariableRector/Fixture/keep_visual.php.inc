<?php

namespace Rector\CodeQuality\Tests\Rector\Return_\SimplifyUselessVariableRector\Fixture;

// covers https://github.com/Sylius/Sylius/pull/9902/files#r231437165
function simplifyUselessVariable5()
{
    $shippingTotal = getAdjustmentsTotal(AdjustmentInterface::SHIPPING_ADJUSTMENT);
    $shippingTotal += getAdjustmentsTotal(AdjustmentInterface::ORDER_SHIPPING_PROMOTION_ADJUSTMENT);
    $shippingTotal += getAdjustmentsTotal(AdjustmentInterface::TAX_ADJUSTMENT);

    return $shippingTotal;
}
