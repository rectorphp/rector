<?php

namespace Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\ContainerInterface;
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
     * @var ContainerInterface
     */
    private static $cachedContainer;

    /**
     * @var ContainerInterface
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
