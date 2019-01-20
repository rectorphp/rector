<?php

namespace Rector\Php\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\ComplextArray;

/**
 * @param null|Foo[] $one
 * @param null|Foo[]|Bar[] $two
 * @param Foo[][] $three
 * @param Foo[]|Bar[][]|Baz[][][][] $four
 * @param Foo[]|iterable $five
 */
function someFunction($one, $two, $three, $four, $five)
{
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FunctionLike\ParamTypeDeclarationRector\Fixture\ComplextArray;

/**
 * @param null|Foo[] $one
 * @param null|Foo[]|Bar[] $two
 * @param Foo[][] $three
 * @param Foo[]|Bar[][]|Baz[][][][] $four
 * @param Foo[]|iterable $five
 */
function someFunction(?array $one, ?array $two, array $three, array $four, array $five)
{
}

?>
