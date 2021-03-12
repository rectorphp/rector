<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\Fixture;

use Doctrine\ORM\EntityManager;
use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\Source\ClassMetadataFactory;

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

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\Fixture;

use Doctrine\ORM\EntityManager;
use Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\Source\ClassMetadataFactory;

class MyMetadataFactory extends ClassMetadataFactory
{
    public function setEntityManager(\Doctrine\ORM\EntityManagerInterface $emanager)
    {
        $this->em = $emanager;
    }
}

?>
