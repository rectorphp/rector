<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\IterableFunction;

function iterableFunction($value)
{
    yield 1;
    yield 2;
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\IterableFunction;

function iterableFunction($value): \Generator
{
    yield 1;
    yield 2;
}

?>
