<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory;

use PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareDataProviderTagValueNode;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

final class PHPUnitDataProviderDocNodeFactory
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
            $tokenIterator->dropSavePoint();
            return $this->parseDataProviderTagValue($tokenIterator);
        } catch (ParserException $parserException) {
            $tokenIterator->rollback();
            $description = $this->privatesCaller->callPrivateMethod(
                $this->phpDocParser,
                'parseOptionalDescription',
                $tokenIterator
            );

            return new InvalidTagValueNode($description, $parserException);
        }
    }

    public function setPhpDocParser(PhpDocParser $phpDocParser): void
    {
        $this->phpDocParser = $phpDocParser;
    }

    /**
     * Override of parent private method to allow reference: https://github.com/rectorphp/rector/pull/1735
     */
    private function parseDataProviderTagValue(TokenIterator $tokenIterator): AttributeAwareDataProviderTagValueNode
    {
        $method = $this->privatesCaller->callPrivateMethod(
            $this->phpDocParser,
            'parseOptionalDescription',
            $tokenIterator
        );

        return new AttributeAwareDataProviderTagValueNode($method);
    }
}
