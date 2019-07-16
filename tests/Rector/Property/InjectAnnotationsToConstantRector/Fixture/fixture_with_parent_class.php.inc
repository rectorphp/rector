<?php

namespace Rector\Tests\Rector\Property\InjectAnnotationsToConstantRector\Fixture;

use Doctrine\ORM\EntityManagerInterface;

class ParentController
{
    protected const DEPENDENCIES = [];
}

class ChildController extends ParentController
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

class ParentController
{
    protected const DEPENDENCIES = [];
}

class ChildController extends ParentController
{
    protected const DEPENDENCIES = parent::DEPENDENCIES + ['entityManager' => \Doctrine\ORM\EntityManagerInterface::class];
    /**
     *
     * @var EntityManagerInterface
     */
    private $entityManager;
}

?>
