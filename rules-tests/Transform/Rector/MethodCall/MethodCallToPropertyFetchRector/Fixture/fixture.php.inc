<?php

namespace Rector\Tests\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector\Fixture;

use Rector\Tests\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector\Source\RenameToProperty;

final class Fixture
{
    public function run(RenameToProperty $renameToProperty)
    {
        $entityManager = $renameToProperty->getEntityManager();
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector\Fixture;

use Rector\Tests\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector\Source\RenameToProperty;

final class Fixture
{
    public function run(RenameToProperty $renameToProperty)
    {
        $entityManager = $renameToProperty->entityManager;
    }
}

?>
