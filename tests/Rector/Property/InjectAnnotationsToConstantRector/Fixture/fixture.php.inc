<?php

namespace Rector\Tests\Rector\Property\InjectAnnotationsToConstantRector\Fixture;

use Doctrine\ORM\EntityManagerInterface;

class SomeController
{
    /**
     * @Inject
     *
     * @var EntityManagerInterface
     */
    private $entityManager;
}

?>
-----
<?php

namespace Rector\Tests\Rector\Property\InjectAnnotationsToConstantRector\Fixture;

use Doctrine\ORM\EntityManagerInterface;

class SomeController
{
    protected const DEPENDENCIES = ['entityManager' => \Doctrine\ORM\EntityManagerInterface::class];
    /**
     *
     * @var EntityManagerInterface
     */
    private $entityManager;
}

?>
