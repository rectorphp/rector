<?php

namespace Rector\Tests\Sensio\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

final class SkipAlreadyRouteAnnotation
{
    /**
     * @Route()
     */
    public function run()
    {
    }
}
