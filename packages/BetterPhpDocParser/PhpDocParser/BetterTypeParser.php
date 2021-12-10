<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocParser;

use PHPStan\PhpDocParser\Ast\Type\TypeNode;
use PHPStan\PhpDocParser\Parser\ConstExprParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use PHPStan\PhpDocParser\Parser\TypeParser;
use Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\BetterPhpDocParser\ValueObject\StartAndEnd;
final class BetterTypeParser extends \PHPStan\PhpDocParser\Parser\TypeParser
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory
     */
    private $tokenIteratorFactory;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocInfo\TokenIteratorFactory $tokenIteratorFactory, ?\PHPStan\PhpDocParser\Parser\ConstExprParser $constExprParser = null)
    {
        $this->tokenIteratorFactory = $tokenIteratorFactory;
        parent::__construct($constExprParser);
    }
    public function parse(\PHPStan\PhpDocParser\Parser\TokenIterator $tokenIterator) : \PHPStan\PhpDocParser\Ast\Type\TypeNode
    {
        $betterTokenIterator = $this->tokenIteratorFactory->createFromTokenIterator($tokenIterator);
        $startPosition = $betterTokenIterator->currentPosition();
        $typeNode = parent::parse($betterTokenIterator);
        $endPosition = $betterTokenIterator->currentPosition();
        $startAndEnd = new \Rector\BetterPhpDocParser\ValueObject\StartAndEnd($startPosition, $endPosition);
        $typeNode->setAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::START_AND_END, $startAndEnd);
        return $typeNode;
    }
}
