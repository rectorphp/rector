<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\MethodCall\ChangeSetParametersArrayToArrayCollectionRector\Fixture;

use Doctrine\ORM\EntityRepository;

class SomeRepository extends EntityRepository
{
    public function getSomething()
    {
        return $this
            ->createQueryBuilder('sm')
            ->select('sm')
            ->where('sm.foo = :bar')
            ->setParameters([
                'bar' => 'baz',
            ])
            ->getQuery()
            ->getResult()
            ;
    }
}
?>
-----
<?php

namespace Rector\DoctrineCodeQuality\Tests\Rector\MethodCall\ChangeSetParametersArrayToArrayCollectionRector\Fixture;

use Doctrine\ORM\EntityRepository;

class SomeRepository extends EntityRepository
{
    public function getSomething()
    {
        return $this
            ->createQueryBuilder('sm')
            ->select('sm')
            ->where('sm.foo = :bar')
            ->setParameters(new \Doctrine\Common\Collections\ArrayCollection([new \Doctrine\ORM\Query\Parameter('bar', 'baz')]))
            ->getQuery()
            ->getResult()
            ;
    }
}
?>
