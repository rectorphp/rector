<?php

namespace Rector\Jms\Tests\Rector\Property\JmsInjectAnnotationRector\Wrong;

use JMS\DiExtraBundle\Annotation as DI;

class ClassWithPublicInjects
{
    /**
     * @DI\Inject
     */
    public $translator;
}
