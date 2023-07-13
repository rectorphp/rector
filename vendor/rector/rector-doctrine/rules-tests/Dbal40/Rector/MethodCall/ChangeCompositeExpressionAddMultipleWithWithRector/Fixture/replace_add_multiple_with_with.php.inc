<?php

namespace Rector\Doctrine\Tests\Dbal40\Rector\MethodCall\ChangeCompositeExpressionAddMultipleWithWithRector\Fixture;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\ORM\EntityRepository;

final class SomeRepository extends EntityRepository
{
    public function getSomething(array $parts)
    {
        $compositeExpression = CompositeExpression::and('', ...$parts);
        $compositeExpression->addMultiple($parts);
    }
}

?>
-----
<?php

namespace Rector\Doctrine\Tests\Rector\MethodCall\ChangeCompositeExpressionAddMultipleWithWithRector\Fixture;

use Doctrine\DBAL\Query\Expression\CompositeExpression;
use Doctrine\ORM\EntityRepository;

final class SomeRepository extends EntityRepository
{
    public function getSomething(array $parts)
    {
        $compositeExpression = CompositeExpression::and('', ...$parts);
        $compositeExpression->with(...$parts);
    }
}

?>
