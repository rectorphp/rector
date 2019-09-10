<?php

namespace Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Fixture;

class SkipSameNamespacedUsedClass
{
    /**
     * @return \Rector\CodingStyle\Tests\Rector\Namespace_\ImportFullyQualifiedNamesRector\Source\SharedShortName
     */
    public function run(): SharedShortName
    {
    }
}
