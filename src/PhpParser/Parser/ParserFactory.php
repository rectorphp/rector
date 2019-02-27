<?php declare(strict_types=1);

namespace Rector\PhpParser\Parser;

use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory as NikicParserFactory;

final class ParserFactory
{
    /**
     * @var Lexer
     */
    private $lexer;

    /**
     * @var NikicParserFactory
     */
    private $nikicParserFactory;

    public function __construct(Lexer $lexer, NikicParserFactory $nikicParserFactory)
    {
        $this->lexer = $lexer;
        $this->nikicParserFactory = $nikicParserFactory;
    }

    public function create(): Parser
    {
        return $this->nikicParserFactory->create(NikicParserFactory::PREFER_PHP7, $this->lexer, [
            'useIdentifierNodes' => true,
            'useConsistentVariableNodes' => true,
            'useExpressionStatements' => true,
            'useNopStatements' => false,
        ]);
    }
}
