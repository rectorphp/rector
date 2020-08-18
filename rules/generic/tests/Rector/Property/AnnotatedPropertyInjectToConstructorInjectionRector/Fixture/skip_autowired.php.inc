<?php

namespace Rector\Generic\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

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
