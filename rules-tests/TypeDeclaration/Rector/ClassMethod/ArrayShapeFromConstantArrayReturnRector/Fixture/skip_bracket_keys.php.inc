<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

final class SkipBracketKeys
{
    public function run(string $name)
    {
        return ['{someKey}' => $name, '[someKey]' => $name];
    }
}
