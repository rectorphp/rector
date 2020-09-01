<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory;

use PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareParamTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Ast\AttributeAwareNodeFactory;
use Rector\BetterPhpDocParser\PhpDocParser\AnnotationContentResolver;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

/**
 * Same as original + also allows "&" reference: https://github.com/rectorphp/rector/pull/1735
 */
final class ParamPhpDocNodeFactory
{
    /**
     * @var PrivatesAccessor
     */
    private $privatesAccessor;

    /**
     * @var PrivatesCaller
     */
    private $privatesCaller;

    /**
     * @var PhpDocParser
     */
    private $phpDocParser;

    /**
     * @var AttributeAwareNodeFactory
     */
    private $attributeAwareNodeFactory;

    /**
     * @var AnnotationContentResolver
     */
    private $annotationContentResolver;

    public function __construct(
        AnnotationContentResolver $annotationContentResolver,
        AttributeAwareNodeFactory $attributeAwareNodeFactory,
        PrivatesAccessor $privatesAccessor,
        PrivatesCaller $privatesCaller
    ) {
        $this->privatesAccessor = $privatesAccessor;
        $this->privatesCaller = $privatesCaller;
        $this->attributeAwareNodeFactory = $attributeAwareNodeFactory;
        $this->annotationContentResolver = $annotationContentResolver;
    }

    public function createFromTokens(TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        try {
            $tokenIterator->pushSavePoint();
            $attributeAwareParamTagValueNode = $this->parseParamTagValue($tokenIterator);
            $tokenIterator->dropSavePoint();

            return $attributeAwareParamTagValueNode;
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
    private function parseParamTagValue(TokenIterator $tokenIterator): AttributeAwareParamTagValueNode
    {
        $originalTokenIterator = clone $tokenIterator;
        $annotationContent = $this->annotationContentResolver->resolveFromTokenIterator($originalTokenIterator);

        $typeParser = $this->privatesAccessor->getPrivateProperty($this->phpDocParser, 'typeParser');

        $type = $typeParser->parse($tokenIterator);

        $isVariadic = $tokenIterator->tryConsumeTokenType(Lexer::TOKEN_VARIADIC);

        // extra value over parent
        $isReference = $tokenIterator->tryConsumeTokenType(Lexer::TOKEN_REFERENCE);

        $parameterName = $this->privatesCaller->callPrivateMethod(
            $this->phpDocParser,
            'parseRequiredVariableName',
            $tokenIterator
        );
        $description = $this->privatesCaller->callPrivateMethod(
            $this->phpDocParser,
            'parseOptionalDescription',
            $tokenIterator
        );

        $type = $this->attributeAwareNodeFactory->createFromNode($type, $annotationContent);

        return new AttributeAwareParamTagValueNode($type, $isVariadic, $parameterName, $description, $isReference);
    }
}
