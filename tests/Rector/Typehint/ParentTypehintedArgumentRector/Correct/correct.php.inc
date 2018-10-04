<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Typehint\ParentTypehintedArgumentRector\Wrong;

use Rector\Tests\Rector\Typehint\ParentTypehintedArgumentRector\Source\ParserInterface;

class SomeClassImplementingParserInterface implements ParserInterface
{
    public function parse(string $code, \PhpParser\ErrorHandler $errorHandler = null)
    {
    }
}
