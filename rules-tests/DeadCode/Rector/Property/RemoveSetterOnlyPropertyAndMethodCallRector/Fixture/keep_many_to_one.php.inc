<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

use Rector\Tests\DeadCode\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Source\PostTemplate;
use Doctrine\ORM\Mapping as ORM;

class KeepManyToOne
{
    /**
     * @var \Rector\Tests\DeadCode\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Source\PostTemplate
     * @ORM\ManyToOne(targetEntity="Rector\Tests\DeadCode\Rector\Property\RemoveSetterOnlyPropertyAndMethodCallRector\Source\PostTemplate", inversedBy="postTemplateBuildings", cascade={"persist", "merge"})
     */
    private $postTemplate;

    public function setPostTemplate(PostTemplate $postTemplate)
    {
        $this->postTemplate = $postTemplate;
    }
}
