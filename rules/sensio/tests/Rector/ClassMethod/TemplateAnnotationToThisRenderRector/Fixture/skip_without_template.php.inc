<?php

namespace Rector\Sensio\Tests\Rector\ClassMethod\TemplateAnnotationToThisRenderRector\Fixture;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Routing\Annotation\Route;

class WithoutBaseClassAndTemplate
{
    /**
     * @Route("/route", name="route")
     */
    public function index(): array
    {
        return [];
    }
}
