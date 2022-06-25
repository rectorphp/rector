<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnTypeFromStrictTypedCallRector\Fixture;

final class SkipFalseReportType
{
    public function isBoolean()
    {
        return $this->fakedTypes();
    }

    /**
     * @return bool
     */
    private function fakedTypes()
    {
        return 100;
    }
}
