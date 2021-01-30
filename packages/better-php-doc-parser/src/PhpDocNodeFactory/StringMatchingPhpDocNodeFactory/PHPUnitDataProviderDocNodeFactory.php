<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\StringMatchingPhpDocNodeFactory;

use PHPStan\PhpDocParser\Ast\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagNode;
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Contract\PhpDocParserAwareInterface;
use Rector\BetterPhpDocParser\Contract\StringTagMatchingPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\PHPUnit\PHPUnitDataProviderTagValueNode;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

final class PHPUnitDataProviderDocNodeFactory implements StringTagMatchingPhpDocNodeFactoryInterface, PhpDocParserAwareInterface
{
    /**
     * @var PrivatesCaller
     */
    private $privatesCaller;

    /**
     * @var PhpDocParser
     */
    private $phpDocParser;

    public function __construct(PrivatesCaller $privatesCaller)
    {
        $this->privatesCaller = $privatesCaller;
    }

    public function createFromTokens(TokenIterator $tokenIterator): ?Node
    {
        try {
            $tokenIterator->pushSavePoint();
            $phpUnitDataProviderTagValueNode = $this->parseDataProviderTagValue($tokenIterator);
            $tokenIterator->dropSavePoint();

            return $phpUnitDataProviderTagValueNode;
        } catch (ParserException $parserException) {
            $tokenIterator->rollback();
            $description = $this->privatesCaller->callPrivateMethod(
                $this->phpDocParser,
                'parseOptionalDescription',
                [$tokenIterator]
            );

            $invalidTagValueNode = new InvalidTagValueNode($description, $parserException);
            return new PhpDocTagNode('', $invalidTagValueNode);
        }
    }

    /**
     * @deprecated Refactor to remove dependency on phpdoc parser
     */
    public function setPhpDocParser(PhpDocParser $phpDocParser): void
    {
        $this->phpDocParser = $phpDocParser;
    }

    public function match(string $tag): bool
    {
        return strtolower($tag) === PHPUnitDataProviderTagValueNode::NAME;
    }

    /**
     * Override of parent private method to allow reference: https://github.com/rectorphp/rector/pull/1735
     */
    private function parseDataProviderTagValue(TokenIterator $tokenIterator): PHPUnitDataProviderTagValueNode
    {
        $method = $this->privatesCaller->callPrivateMethod(
            $this->phpDocParser,
            'parseOptionalDescription',
            [$tokenIterator]
        );

        return new PHPUnitDataProviderTagValueNode($method);
    }
}
