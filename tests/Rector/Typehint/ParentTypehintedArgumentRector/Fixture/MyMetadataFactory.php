<?php

namespace Rector\Tests\Rector\Typehint\ParentTypehintedArgumentRector\Fixture;

use Rector\Tests\Rector\Typehint\ParentTypehintedArgumentRector\Source\ClassMetadataFactory;

class MyMetadataFactory extends ClassMetadataFactory
{
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Typehint\ParentTypehintedArgumentRector\Fixture;

use Rector\Tests\Rector\Typehint\ParentTypehintedArgumentRector\Source\ClassMetadataFactory;

class MyMetadataFactory extends ClassMetadataFactory
{
    public function setEntityManager(\Doctrine\ORM\EntityManagerInterface $em)
    {
        $this->em = $em;
    }
}

?>
