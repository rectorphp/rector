<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Parser;

use PhpParser\Lexer\Emulative;

/**
 * This Lexer allows Format-perserving AST Transformations.
 * @see https://github.com/nikic/PHP-Parser/issues/344#issuecomment-298162516
 */
final class PhpParserLexerFactory
{
    public function create(): Emulative
    {
        return new Emulative([
            'usedAttributes' => ['comments', 'startLine', 'endLine', 'startTokenPos', 'endTokenPos'],
            // do not set php version, so we have a lexer that can parse any PHP version available,
            // helps with issues like https://github.com/rectorphp/rector/issues/6593
        ]);
    }
}
