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

function iterableFunction($value): \Iterator
{
    yield 1;
    yield 2;
}

?>
