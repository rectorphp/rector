<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

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
