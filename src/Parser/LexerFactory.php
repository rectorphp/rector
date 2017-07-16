<?php declare(strict_types=1);

namespace Rector\Parser;

use PhpParser\Lexer;
use PhpParser\Lexer\Emulative;

final class LexerFactory
{
    public function create(): Lexer
    {
        return new Emulative([
            'usedAttributes' => [
                'comments',
                'startLine', 'endLine',
                'startTokenPos', 'endTokenPos',
            ],
        ]);
    }
}
