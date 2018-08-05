<?php declare(strict_types=1);

namespace Rector\Tests\Rector\Typehint\ParentTypehintedArgumentRector\Source;

interface ParserInterface
{
    public function parse(string $code, \PhpParser\ErrorHandler $errorHandler = null);
}
