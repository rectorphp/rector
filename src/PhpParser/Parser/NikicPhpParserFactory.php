<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Parser;

use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory;

final class NikicPhpParserFactory
{
    public function __construct(
        private Lexer $lexer,
        private ParserFactory $parserFactory
    ) {
    }

    public function create(): Parser
    {
        return $this->parserFactory->create(ParserFactory::PREFER_PHP7, $this->lexer, [
            'useIdentifierNodes' => true,
            'useConsistentVariableNodes' => true,
            'useExpressionStatements' => true,
            'useNopStatements' => false,
        ]);
    }
}
