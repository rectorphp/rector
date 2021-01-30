<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\StringMatchingPhpDocNodeFactory;

use PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\DataProviderTagValueNode;
use Rector\BetterPhpDocParser\Contract\PhpDocParserAwareInterface;
use Rector\BetterPhpDocParser\Contract\StringTagMatchingPhpDocNodeFactoryInterface;
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

    public function createFromTokens(TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        try {
            $tokenIterator->pushSavePoint();
            $attributeAwareDataProviderTagValueNode = $this->parseDataProviderTagValue($tokenIterator);
            $tokenIterator->dropSavePoint();

            return $attributeAwareDataProviderTagValueNode;
        } catch (ParserException $parserException) {
            $tokenIterator->rollback();
            $description = $this->privatesCaller->callPrivateMethod(
                $this->phpDocParser,
                'parseOptionalDescription',
                [$tokenIterator]
            );

            return new InvalidTagValueNode($description, $parserException);
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
        return strtolower($tag) === '@dataprovider';
    }

    /**
     * Override of parent private method to allow reference: https://github.com/rectorphp/rector/pull/1735
     */
    private function parseDataProviderTagValue(TokenIterator $tokenIterator): DataProviderTagValueNode
    {
        $method = $this->privatesCaller->callPrivateMethod(
            $this->phpDocParser,
            'parseOptionalDescription',
            [$tokenIterator]
        );

        return new DataProviderTagValueNode($method);
    }
}
