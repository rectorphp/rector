<?php

namespace Rector\Jms\Tests\Rector\Property\JmsInjectAnnotationRector\Wrong;

use JMS\DiExtraBundle\Annotation as DI;

class SomeController2
{
    /**
     * @DI\Inject("Rector\Symfony\Tests\Rector\FrameworkBundle\AbstractToConstructorInjectionRectorSource\SomeEntityManager")
     */
    private $entityManager;
}
