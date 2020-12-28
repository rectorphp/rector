<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

class Fixture3
{
    public function exists(): bool
    {
        $order = $someQueryBuilder->getQuery()->getOneOrNullResult();
        return $order ? true : false;
    }

    public function go(): bool
    {
        $order = $someQueryBuilder->getQuery()->getOneOrNullResult();
        return $order ? false : true;
    }

    public function boolAlready(): bool
    {
        $order = false;
        return $order ? true : false;
    }

    public function switchBack(): bool
    {
        $order = false;
        return $order ? false : true;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Ternary\UnnecessaryTernaryExpressionRector\Fixture;

class Fixture3
{
    public function exists(): bool
    {
        $order = $someQueryBuilder->getQuery()->getOneOrNullResult();
        return (bool) $order;
    }

    public function go(): bool
    {
        $order = $someQueryBuilder->getQuery()->getOneOrNullResult();
        return !(bool) $order;
    }

    public function boolAlready(): bool
    {
        $order = false;
        return $order;
    }

    public function switchBack(): bool
    {
        $order = false;
        return !$order;
    }
}

?>
