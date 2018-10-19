<?php

namespace Rector\Jms\Tests\Rector\Property\JmsInjectAnnotationRector\Wrong;

use JMS\DiExtraBundle\Annotation as DI;

class ClassWithInjects35
{
    /**
     * @DI\Inject
     */
    private $translator;
}
