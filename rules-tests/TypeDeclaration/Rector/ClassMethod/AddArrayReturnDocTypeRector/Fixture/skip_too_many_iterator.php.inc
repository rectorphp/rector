<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use Iterator;

final class SkipTooManyIterator
{
    public function provide(): Iterator
    {
        yield [
            __DIR__ . '/config/main_config_with_only_imports.php', [
                'old_2' => 'new_2',
                'old_1' => 'new_1',
            ],
        ];

        yield [
            __DIR__ . '/config/one_set_with_own_rename.php', [
                'PHPUnit_Framework_MockObject_Stub' => 'PHPUnit\Framework\MockObject\Stub',
                'PHPUnit_Framework_MockObject_Stub_Return' => 'PHPUnit\Framework\MockObject\Stub\ReturnStub',
                'PHPUnit_Framework_MockObject_MockObject' => 'PHPUnit\Framework\MockObject\MockObject',
                'Old' => 'New',
            ],
        ];
    }
}
