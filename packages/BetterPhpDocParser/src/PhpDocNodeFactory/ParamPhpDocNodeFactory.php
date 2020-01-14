<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\InvalidTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use PHPStan\PhpDocParser\Parser\ParserException;
use PHPStan\PhpDocParser\Parser\PhpDocParser;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\AttributeAwarePhpDoc\Ast\PhpDoc\AttributeAwareParamTagValueNode;
use Rector\BetterPhpDocParser\Contract\NameAwarePhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocParserAwareInterface;
use Symplify\PackageBuilder\Reflection\PrivatesAccessor;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

final class ParamPhpDocNodeFactory implements NameAwarePhpDocNodeFactoryInterface, PhpDocParserAwareInterface
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

    public function __construct(PrivatesAccessor $privatesAccessor, PrivatesCaller $privatesCaller)
    {
        $this->privatesAccessor = $privatesAccessor;
        $this->privatesCaller = $privatesCaller;
    }

    public function getName(): string
    {
        return 'param';
    }

    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        try {
            $tokenIterator->pushSavePoint();
            $tagValue = $this->parseParamTagValue($tokenIterator);
            $tokenIterator->dropSavePoint();

            return $tagValue;
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
    private function parseParamTagValue(TokenIterator $tokenIterator): ParamTagValueNode
    {
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

        return new AttributeAwareParamTagValueNode($type, $isVariadic, $parameterName, $description, $isReference);
    }
}
