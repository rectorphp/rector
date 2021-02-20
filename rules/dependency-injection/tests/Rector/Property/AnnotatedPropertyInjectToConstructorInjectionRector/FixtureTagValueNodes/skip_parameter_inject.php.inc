<?php

namespace Rector\DependencyInjection\Tests\Rector\Property\AnnotatedPropertyInjectToConstructorInjectionRector\FixtureTagValueNodes;

use JMS\DiExtraBundle\Annotation as DI;

class SkipParameterInject
{
    /**
     * @DI\Inject("%kernel.cache_dir%")
     */
    private $cacheDir;
}
