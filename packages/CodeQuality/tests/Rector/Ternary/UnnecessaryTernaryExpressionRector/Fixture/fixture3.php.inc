<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

class EdgeCases
{
    public function exists(): bool
    {
        $order = $someQueryBuilder->getQuery()->getOneOrNullResult();
        return $order ? true : false;

        return $order ? false : true;
    }

    public function boolAlready(): bool
    {
        $order = false;
        return $order ? true : false;

        return $order ? false : true;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

class EdgeCases
{
    public function exists(): bool
    {
        $order = $someQueryBuilder->getQuery()->getOneOrNullResult();
        return (bool) $order;

        return !(bool) $order;
    }

    public function boolAlready(): bool
    {
        $order = false;
        return $order;

        return !$order;
    }
}

?>
