<?php

declare(strict_types=1);

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Source;

abstract class SomeParent
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $typedName;

    protected bool $anAlreadyReplacedPropertyInParentClass = false;
}
