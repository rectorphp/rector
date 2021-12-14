<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

use Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source\AbstractParentClass;
use Symfony\Component\Validator\Constraints as Assert;

final class SkipProtectedPropertyWithParent extends AbstractParentClass
{
    /**
     * @var string
     * @Assert\Choice({"chalet", "apartment"})
     */
    protected $type;
}
