<?php

declare(strict_types=1);

namespace Rector\Tests\CodingStyle\Rector\Namespace_\ImportFullyQualifiedNamesRector\FixtureNameImportingOnChangedFiles;

class SkipAutoImportNameNoChange
{
    /**
     * @var \SomeAnother\AnotherClass
     */
    public $some;

    public function __construct(\SomeAnother\AnotherClass $some)
    {
        $this->some = $some;
    }
}
?>
