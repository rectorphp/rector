<?php

namespace Rector\Tests\CodeQuality\Rector\Variable\MoveVariableDeclarationNearReferenceRector\Source;

class MyException extends \Exception
{
    public static function notFound()
    {
        return new self('Page not found');
    }
}
