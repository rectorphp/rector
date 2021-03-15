<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector\Fixture;

use Doctrine\ORM\EntityManager;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector\Source\ClassMetadataFactory;

class MyMetadataFactory extends ClassMetadataFactory
{
    public function setEntityManager(EntityManager $emanager)
    {
        $this->em = $emanager;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector\Fixture;

use Doctrine\ORM\EntityManager;
use Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector\Source\ClassMetadataFactory;

class MyMetadataFactory extends ClassMetadataFactory
{
    public function setEntityManager(\Doctrine\ORM\EntityManagerInterface $emanager)
    {
        $this->em = $emanager;
    }
}

?>
