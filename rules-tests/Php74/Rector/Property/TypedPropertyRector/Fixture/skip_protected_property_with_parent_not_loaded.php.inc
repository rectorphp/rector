<?php

namespace Rector\Tests\Php74\Rector\Property\TypedPropertyRector\Fixture;

final class SkipProtectedPropertyWithParentNotLoaded extends NotLoadedClass
{
    /**
     * @var string
     */
    protected $type;
}
