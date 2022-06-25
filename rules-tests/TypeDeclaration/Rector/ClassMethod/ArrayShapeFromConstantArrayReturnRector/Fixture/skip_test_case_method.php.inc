<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\ArrayShapeFromConstantArrayReturnRector\Fixture;

use PHPUnit\Framework\TestCase;

final class SkipTestCaseMethod extends TestCase
{
    public function getData(): array
    {
        return [
            'name' => 'first one',
            'surname' => 'second two',
        ];
    }
}
