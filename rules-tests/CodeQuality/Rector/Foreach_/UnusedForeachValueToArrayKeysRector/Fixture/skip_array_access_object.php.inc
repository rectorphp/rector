<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\UnusedForeachValueToArrayKeysRector\Fixture;

use PhpCsFixer\Doctrine\Annotation\Token;
use PhpCsFixer\Doctrine\Annotation\Tokens;

final class SkipArrayAccessObject
{
    /**
     * @param Tokens<Token> $tokens
     */
    public function run(Tokens $tokens)
    {
        $indexes = [];
        foreach ($tokens as $index => $token) {
            $indexes[] = $index;
        }

        return $indexes;
    }
}
