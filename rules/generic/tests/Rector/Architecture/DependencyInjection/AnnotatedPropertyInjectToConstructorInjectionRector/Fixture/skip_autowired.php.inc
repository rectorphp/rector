<?php

namespace Rector\Generic\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

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
