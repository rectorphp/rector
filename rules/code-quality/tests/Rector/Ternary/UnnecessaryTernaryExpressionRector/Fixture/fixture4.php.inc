<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

class Fixture4
{
    public function exists(): bool
    {
        return $order ? true : false; // not fixable
        return $order->isValid() ? true : false; // not fixable
        return $order === null ? true : false; // fixable
        return $order->getPrice() > 0 ? true : false; // fixable
        return $order || $oldOrder ? true : false; // fixable
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

class Fixture4
{
    public function exists(): bool
    {
        return (bool) $order; // not fixable
        return (bool) $order->isValid(); // not fixable
        return $order === null; // fixable
        return $order->getPrice() > 0; // fixable
        return $order || $oldOrder; // fixable
    }
}

?>
