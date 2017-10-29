<?php declare(strict_types=1);

namespace SomeNamespace;

use Doctrine\ORM\EntityManager;

class MyMetadataFactory extends \Doctrine\ORM\Mapping\ClassMetadataFactory
{
    public function setEntityManager(EntityManager $em)
    {
        $this->em = $em;
    }
}
