<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Tests\DowngradePhp72\Rector\Class_\DowngradeParameterTypeWideningRector\Source\UniqueType;
use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\ILoader;
use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\SingleSomeClass;
use SplFileInfo;

class KeepVariousTypes
{
    /**
     * @var string
     */
    private $filenameWithoutDate;

    /**
     * @var SingleSomeClass[]
     */
    private $singleSomeClasses;

    /**
     * @var ILoader
     */
    private $loader;

    /**
     * @var UniqueType
     */
    private static $cachedContainer;

    /**
     * @var UniqueType
     */
    private static $smartContainer;

    /**
     * @var callable|IPresenter
     */
    private $presenter;

    public function __construct(
        SplFileInfo $firstFile,
        SplFileInfo $secondFile
    ) {
    }
}
