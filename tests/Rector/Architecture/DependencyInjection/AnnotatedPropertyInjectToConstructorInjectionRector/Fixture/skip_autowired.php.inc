<?php

namespace Rector\Core\Tests\Rector\Architecture\DependencyInjection\AnnotatedPropertyInjectToConstructorInjectionRector\Fixture;

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
