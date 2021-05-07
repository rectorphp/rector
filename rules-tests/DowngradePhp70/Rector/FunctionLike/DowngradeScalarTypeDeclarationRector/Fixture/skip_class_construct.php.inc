<?php

namespace Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\Fixture;

use Rector\Tests\DowngradePhp70\Rector\FunctionLike\DowngradeScalarTypeDeclarationRector\Source\SomeObjectService;

final class SkipClassConstruct
{
    private $someObjectService;

    public function __construct(SomeObjectService $someObjectService)
    {
        $this->someObjectService = $someObjectService;
    }
}
