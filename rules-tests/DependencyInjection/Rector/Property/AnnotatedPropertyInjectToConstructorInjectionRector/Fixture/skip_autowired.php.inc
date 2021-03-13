<?php

namespace Rector\Tests\DependencyInjection\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

use DateTimeInterface;
use stdClass;

class SkipAutowired
{
    /**
     * @var stdClass
     * @autowired
     */
    public $property;
}
