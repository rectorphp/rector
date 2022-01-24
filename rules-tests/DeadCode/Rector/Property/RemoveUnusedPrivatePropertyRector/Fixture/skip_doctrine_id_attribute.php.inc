<?php

namespace Rector\Tests\DeadCode\Rector\Property\RemoveUnusedPrivatePropertyRector\Fixture;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Id;

class SkipDoctrineIdAttribute
{
    #[Id, Column(type: "integer")]
    private $id = null;
}
