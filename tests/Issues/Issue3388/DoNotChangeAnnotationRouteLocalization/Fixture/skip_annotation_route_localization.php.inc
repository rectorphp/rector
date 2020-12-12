<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue3388\DoNotChangeAnnotationRouteLocalization\Fixture;

use Symfony\Component\Routing\Annotation\Route;

class SkipAnnotationRouteLocalization
{
    /**
     * @Route({
     *     "en": "/about-us",
     *     "nl": "/over-ons"
     * }, name="about_us")
     */
    public function about()
    {
    }
}

?>
