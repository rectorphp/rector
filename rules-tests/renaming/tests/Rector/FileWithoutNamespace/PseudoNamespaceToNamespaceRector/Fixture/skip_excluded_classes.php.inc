<?php

namespace Rector\Renaming\Tests\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Fixture;

class SkipExcludedClasses
{
    /**
     * @return Synapse|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getSynapseMock(int $output = 2): \PHPUnit_Framework_MockObject_MockObject
    {
    }
}
