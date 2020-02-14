<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\IterableFunction;

function iterableFunction($value)
{
    yield 1;
    yield 2;
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\IterableFunction;

function iterableFunction($value): iterable
{
    yield 1;
    yield 2;
}

?>
