<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Generator;

class SkipExplicitGeneratorFrom
{
    /**
     * @return Generator<string, string>
     */
    public function run(string $part): Generator
    {
        $result = ['a'];
        parse_str($part, $result);

        yield from $result;
    }
}
