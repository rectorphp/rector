<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

final class SkipReturnTypeIterable
{
    /**
      * @return iterable<string, mixed[]>
      */
    public function run(): iterable
    {
        yield from $this->getData();
    }

    private function getData(): array
    {
        return [];
    }
}
