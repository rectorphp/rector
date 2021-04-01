<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser;

use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\Core\Configuration\CurrentNodeProvider;

final class PlainValueParser
{
    /**
     * @var StaticDoctrineAnnotationParser
     */
    private $staticDoctrineAnnotationParser;

    /**
     * @var ClassAnnotationMatcher
     */
    private $classAnnotationMatcher;

    /**
     * @var CurrentNodeProvider
     */
    private $currentNodeProvider;

    public function __construct(
        ClassAnnotationMatcher $classAnnotationMatcher,
        CurrentNodeProvider $currentNodeProvider
    ) {
        $this->classAnnotationMatcher = $classAnnotationMatcher;
        $this->currentNodeProvider = $currentNodeProvider;
    }

    /**
     * @required
     */
    public function autowirePlainValueParser(StaticDoctrineAnnotationParser $staticDoctrineAnnotationParser): void
    {
        $this->staticDoctrineAnnotationParser = $staticDoctrineAnnotationParser;
    }

    /**
     * @return bool|int|mixed|string
     */
    public function parseValue(BetterTokenIterator $tokenIterator)
    {
        $currentTokenValue = $tokenIterator->currentTokenValue();

        // temporary hackaround multi-line doctrine annotations
        if ($tokenIterator->isCurrentTokenType(Lexer::TOKEN_END)) {
            return $currentTokenValue;
        }

        // consume the token
        $tokenIterator->next();

        // normalize value
        if ($currentTokenValue === 'false') {
            return new ConstExprFalseNode();
        }

        if ($currentTokenValue === 'true') {
            return new ConstExprTrueNode();
        }

        if (is_numeric($currentTokenValue) && (string) (int) $currentTokenValue === $currentTokenValue) {
            return new ConstExprIntegerNode($currentTokenValue);
        }

        while ($tokenIterator->isCurrentTokenType(Lexer::TOKEN_DOUBLE_COLON) ||
            $tokenIterator->isCurrentTokenType(Lexer::TOKEN_IDENTIFIER)
        ) {
            $currentTokenValue .= $tokenIterator->currentTokenValue();
            $tokenIterator->next();
        }

        // nested entity!
        if ($tokenIterator->isCurrentTokenType(Lexer::TOKEN_OPEN_PARENTHESES)) {
            // @todo
            $annotationShortName = $currentTokenValue;
            $values = $this->staticDoctrineAnnotationParser->resolveAnnotationMethodCall($tokenIterator);

            $fullyQualifiedAnnotationClass = $this->classAnnotationMatcher->resolveTagFullyQualifiedName(
                $annotationShortName,
                $this->currentNodeProvider->getNode()
            );

            return new DoctrineAnnotationTagValueNode($fullyQualifiedAnnotationClass, $annotationShortName, $values);
        }

        return $currentTokenValue;
    }
}
