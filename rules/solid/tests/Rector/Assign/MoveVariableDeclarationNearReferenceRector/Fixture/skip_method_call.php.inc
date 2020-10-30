<?php

namespace Rector\SOLID\Tests\Rector\Assign\MoveVariableDeclarationNearReferenceRector\Fixture;

use PhpParser\PrettyPrinter\Standard;

final class SkipMethodCall
{
    public function myMethod(array $nodes)
    {
        $standard = new Standard();
        $printedContents = [];

        foreach ($nodes as $node) {
            $printedContent = $standard->prettyPrint([$node]);
            $printedContents[] = $printedContent;
        }
    }
}
