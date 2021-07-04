<?php

namespace Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Fixture;

use Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Source\ContainerInterface;
use Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Source\EliteManager;
use Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Source\ILoader;
use Rector\Tests\Naming\Rector\Class_\RenamePropertyToMatchTypeRector\Source\SingleSomeClass;
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
     * @var EliteManager
     */
    private static $first;

    /**
     * @var EliteManager
     */
    private static $second;

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
