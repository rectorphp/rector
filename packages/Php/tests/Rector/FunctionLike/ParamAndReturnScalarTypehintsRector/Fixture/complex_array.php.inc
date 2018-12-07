<?php

namespace Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnScalarTypehintsRector\Fixture\ComplextArray;

/**
 * @param null|Foo[] $one
 * @param null|Foo[]|Bar[] $two
 * @param Foo[][] $three
 * @param Foo[]|Bar[][]|Baz[][][][] $four
 * @param Foo[]|iterable $five
 * @return Foo[][]
 */
function someFunction($one, $two, $three, $four, $five)
{
}

/**
 * @return real
 */
function someFunction2()
{
}

/**
 * @return Foo[]|iterable
 */
function someFunction3()
{
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\FunctionLike\ParamAndReturnScalarTypehintsRector\Fixture\ComplextArray;

/**
 * @param null|Foo[] $one
 * @param null|Foo[]|Bar[] $two
 * @param Foo[][] $three
 * @param Foo[]|Bar[][]|Baz[][][][] $four
 * @param Foo[]|iterable $five
 * @return Foo[][]
 */
function someFunction(?array $one, ?array $two, array $three, array $four, array $five): array
{
}

/**
 * @return real
 */
function someFunction2()
{
}

/**
 * @return Foo[]|iterable
 */
function someFunction3(): array
{
}

?>
