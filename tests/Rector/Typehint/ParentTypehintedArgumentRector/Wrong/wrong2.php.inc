<?php declare(strict_types=1);

namespace SomeNamespace;

use Rector\Tests\Rector\Typehint\ParentTypehintedArgumentRector\Source\ClassMetadataFactory;

class MyMetadataFactory extends ClassMetadataFactory
{
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }
}
