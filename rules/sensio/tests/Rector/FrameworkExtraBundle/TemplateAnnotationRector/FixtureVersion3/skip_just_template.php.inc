<?php

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\FixtureVersion3;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SkipJustTemplateController extends AbstractController
{
    /**
     * @Template
     */
    public function indexAction()
    {
        return [];
    }
}
