<?php

declare(strict_types=1);

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source;

abstract class AbstractParentClass
{
    /**
     * @var string
     * @Assert\Choice({"chalet", "apartment"})
     */
    protected $type;
}
