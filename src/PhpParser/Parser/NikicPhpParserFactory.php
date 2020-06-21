<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Parser;

use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory;

final class NikicPhpParserFactory
{
    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var ParserFactory
     */
    private $parserFactory;

    public function __construct(Lexer $lexer, ParserFactory $nikicParserFactory)
    {
        $this->lexer = $lexer;
        $this->parserFactory = $nikicParserFactory;
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
