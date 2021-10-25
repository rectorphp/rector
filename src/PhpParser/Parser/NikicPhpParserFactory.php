<?php

declare (strict_types=1);
namespace Rector\Core\PhpParser\Parser;

use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory;
final class NikicPhpParserFactory
{
    /**
     * @var \PhpParser\ParserFactory
     */
    private $parserFactory;
    /**
     * @var \PhpParser\Lexer
     */
    private $lexer;
    public function __construct(\PhpParser\ParserFactory $parserFactory, \PhpParser\Lexer $lexer)
    {
        $this->parserFactory = $parserFactory;
        $this->lexer = $lexer;
    }
    public function create() : \PhpParser\Parser
    {
        return $this->parserFactory->create(\PhpParser\ParserFactory::PREFER_PHP7, $this->lexer, ['useIdentifierNodes' => \true, 'useConsistentVariableNodes' => \true, 'useExpressionStatements' => \true, 'useNopStatements' => \false]);
    }
}
