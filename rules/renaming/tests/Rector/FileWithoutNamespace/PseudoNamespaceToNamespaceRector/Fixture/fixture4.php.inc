<?php

namespace Rector\Renaming\Tests\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Fixture;

class Fixture4
{
    /**
     * @return Synapse|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getSynapseMock(int $output = 2): \PHPUnit_Framework_MockObject_MockObject
    {
    }
}
