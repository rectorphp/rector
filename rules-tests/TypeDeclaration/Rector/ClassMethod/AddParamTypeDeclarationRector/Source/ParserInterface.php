<?php

declare(strict_types=1);

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddParamTypeDeclarationRector\Source;

interface ParserInterface
{
    public function parse($code, \PhpParser\ErrorHandler $errorHandler = null);
}
