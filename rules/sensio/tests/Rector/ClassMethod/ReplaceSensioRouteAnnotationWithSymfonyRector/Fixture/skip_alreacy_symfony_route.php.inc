<?php

namespace Rector\Sensio\Tests\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

final class SkipAlreacySymfonyRoute
{
    /**
     * @Route()
     */
    public function run()
    {
    }
}
