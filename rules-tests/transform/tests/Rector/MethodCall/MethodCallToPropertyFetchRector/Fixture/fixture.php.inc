<?php

namespace Rector\Transform\Tests\Rector\MethodCall\MethodCallToPropertyFetchRector\Fixture;

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

namespace Rector\Transform\Tests\Rector\MethodCall\MethodCallToPropertyFetchRector\Fixture;

class Fixture
{
    public function run()
    {
        $entityManager = $this->entityManager;
    }
}

?>
