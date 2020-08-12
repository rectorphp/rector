<?php

namespace Rector\Generic\Tests\Rector\MethodCall\MethodCallToPropertyFetchRector\Fixture;

class SomeClass
{
    public function run()
    {
        $entityManager = $this->getEntityManager();
    }
}

?>
-----
<?php

namespace Rector\Generic\Tests\Rector\MethodCall\MethodCallToPropertyFetchRector\Fixture;

class SomeClass
{
    public function run()
    {
        $entityManager = $this->entityManager;
    }
}

?>
