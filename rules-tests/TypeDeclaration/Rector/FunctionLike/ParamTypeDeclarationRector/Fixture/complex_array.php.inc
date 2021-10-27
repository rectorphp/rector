<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\ComplextArray;

/**
 * @param null|Foo[] $one
 * @param null|Foo[]|Bar[] $two
 * @param Foo[][] $three
 * @param Foo[]|Bar[][]|Baz[][][][] $four
 */
function someFunction($one, $two, $three, $four)
{
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\ComplextArray;

/**
 * @param null|Foo[] $one
 * @param null|Foo[]|Bar[] $two
 * @param Foo[][] $three
 * @param Foo[]|Bar[][]|Baz[][][][] $four
 */
function someFunction(?array $one, ?array $two, array $three, array $four)
{
}

?>
