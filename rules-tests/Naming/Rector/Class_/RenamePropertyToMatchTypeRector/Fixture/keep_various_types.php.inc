<?php

namespace Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source\ContainerInterface;
use Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source\ILoader;
use Rector\Naming\Tests\Rector\Class_\RenamePropertyToMatchTypeRector\Source\SingleSomeClass;
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
