<?php

namespace Rector\Renaming\Tests\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector\Fixture;

class SkipClassFromSameNamespace
{
    /**
     * @param \DateTime $time1
     * @return int
     */
    public function test(\DateTime $time1)
    {
        return $time1->getOffset();
    }
}
