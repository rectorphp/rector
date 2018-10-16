<?php declare(strict_types=1);

use Rector\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Source\SomeInterface;
use JMS\DiExtraBundle\Annotation as DI;

class ClassWithInjects7
{
    /**
     * @var SomeInterface
     * @DI\Inject("irelevant")
     */
    private $session;
}
