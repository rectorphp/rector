<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\Source;

use PhpParser\ErrorHandler;
interface ParserInterface
{
    public function parse($code, ErrorHandler $errorHandler = null);
}
