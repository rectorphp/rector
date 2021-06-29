<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ReturnNeverTypeRector\FixturePHP72;

final class SkipNeverAlready
{
    /**
     * @return never
     */
    public function run()
    {
        throw new InvalidException();
    }
}
