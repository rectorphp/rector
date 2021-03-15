<?php

namespace Rector\Tests\Symfony\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector\Fixture;

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
