<?php

namespace Rector\Tests\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector\Fixture;

class Fixture
{
    public function run()
    {
        $entityManager = $this->getEntityManager();
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\MethodCall\MethodCallToPropertyFetchRector\Fixture;

class Fixture
{
    public function run()
    {
        $entityManager = $this->entityManager;
    }
}

?>
