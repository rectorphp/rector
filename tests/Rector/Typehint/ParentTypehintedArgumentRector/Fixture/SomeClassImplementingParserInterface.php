<?php

namespace Rector\Tests\Rector\Typehint\ParentTypehintedArgumentRector\Fixture;

use Rector\Tests\Rector\Typehint\ParentTypehintedArgumentRector\Source\ParserInterface;

class SomeClassImplementingParserInterface implements ParserInterface
{
    public function parse($code, \PhpParser\ErrorHandler $errorHandler = null)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Rector\Typehint\ParentTypehintedArgumentRector\Fixture;

use Rector\Tests\Rector\Typehint\ParentTypehintedArgumentRector\Source\ParserInterface;

class SomeClassImplementingParserInterface implements ParserInterface
{
    public function parse(string $code, \PhpParser\ErrorHandler $errorHandler = null)
    {
    }
}

?>
