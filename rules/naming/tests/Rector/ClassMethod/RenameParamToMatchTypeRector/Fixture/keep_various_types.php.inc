<?php

namespace Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\ContainerInterface;
use Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\ILoader;
use Rector\Naming\Tests\Rector\ClassMethod\RenameParamToMatchTypeRector\Source\SingleSomeClass;
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
