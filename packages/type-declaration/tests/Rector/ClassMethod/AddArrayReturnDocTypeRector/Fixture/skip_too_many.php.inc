<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

class SkipTooMany
{
    /**
     * @return mixed[][]
     */
    public function provideFilesToContent(): array
    {
        return [
            [__DIR__ . '/Yaml/spaces.yml', [
                'address' => [
                    'street' => '742 Evergreen Terrace',
                ],
            ]],
            # arrays
            [__DIR__ . '/Yaml/list.yml', [
                'services' => ['SomeService', 'SomeService'],
            ]],
            [__DIR__ . '/Yaml/array.yml', [
                'services' => [
                    'SomeService' => null,
                ],
            ]],
            # multi lines
            [__DIR__ . '/Yaml/multi-lines.yml', ['perex' => 'Multi' . PHP_EOL . 'line']],
        ];
    }
}
