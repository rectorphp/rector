<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocParser;

use RectorPrefix20220606\PHPStan\PhpDocParser\Ast\Type\TypeNode;
use RectorPrefix20220606\PHPStan\PhpDocParser\Parser\ConstExprParser;
use RectorPrefix20220606\PHPStan\PhpDocParser\Parser\TokenIterator;
use RectorPrefix20220606\PHPStan\PhpDocParser\Parser\TypeParser;
use RectorPrefix20220606\Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use RectorPrefix20220606\Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
final class BetterTypeParser extends TypeParser
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory
     */
    private $tokenIteratorFactory;
    public function __construct(TokenIteratorFactory $tokenIteratorFactory, ?ConstExprParser $constExprParser = null)
    {
        $this->tokenIteratorFactory = $tokenIteratorFactory;
        parent::__construct($constExprParser);
    }
    public function parse(TokenIterator $tokenIterator) : TypeNode
    {
        $betterTokenIterator = $this->tokenIteratorFactory->createFromTokenIterator($tokenIterator);
        $startPosition = $betterTokenIterator->currentPosition();
        $typeNode = parent::parse($betterTokenIterator);
        $endPosition = $betterTokenIterator->currentPosition();
        $startAndEnd = new StartAndEnd($startPosition, $endPosition);
        $typeNode->setAttribute(PhpDocAttributeKey::START_AND_END, $startAndEnd);
        return $typeNode;
    }
}
