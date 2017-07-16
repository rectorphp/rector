<?php declare(strict_types=1);

namespace Rector\Parser;

use PhpParser\Lexer;
use PhpParser\Parser;
use PhpParser\ParserFactory as NikicParserFactory;

final class ParserFactory
{
    /**
     * @var Lexer
     */
    private $lexer;

    public function __construct(Lexer $lexer)
    {
        $this->lexer = $lexer;
    }

    public function create(): Parser
    {
        $nikicParserFactory = new NikicParserFactory;
        return $nikicParserFactory->create(NikicParserFactory::PREFER_PHP7, $this->lexer);
    }
}
