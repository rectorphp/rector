<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use Symfony\Contracts\Service\Attribute\Required;

final class PlainValueParser
{
    private StaticDoctrineAnnotationParser $staticDoctrineAnnotationParser;

    private ArrayParser $arrayParser;

    public function __construct(
        private ClassAnnotationMatcher $classAnnotationMatcher,
        private CurrentNodeProvider $currentNodeProvider
    ) {
    }

    #[Required]
    public function autowirePlainValueParser(
        StaticDoctrineAnnotationParser $staticDoctrineAnnotationParser,
        ArrayParser $arrayParser
    ): void {
        $this->staticDoctrineAnnotationParser = $staticDoctrineAnnotationParser;
        $this->arrayParser = $arrayParser;
    }

    /**
     * @return mixed[]
     */
    public function parseValue(
        BetterTokenIterator $tokenIterator
    ): string | array | ConstExprFalseNode | ConstExprTrueNode | ConstExprIntegerNode | DoctrineAnnotationTagValueNode {
        $currentTokenValue = $tokenIterator->currentTokenValue();

        // temporary hackaround multi-line doctrine annotations
        if ($tokenIterator->isCurrentTokenType(Lexer::TOKEN_END)) {
            return $currentTokenValue;
        }

        // consume the token
        $isOpenCurlyArray = $tokenIterator->isCurrentTokenType(Lexer::TOKEN_OPEN_CURLY_BRACKET);
        if ($isOpenCurlyArray) {
            return $this->arrayParser->parseCurlyArray($tokenIterator);
        }

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
            return $this->parseNestedDoctrineAnnotationTagValueNode($currentTokenValue, $tokenIterator);
        }

        return $currentTokenValue;
    }

    private function parseNestedDoctrineAnnotationTagValueNode(
        string $currentTokenValue,
        BetterTokenIterator $tokenIterator
    ): DoctrineAnnotationTagValueNode {
        // @todo
        $annotationShortName = $currentTokenValue;
        $values = $this->staticDoctrineAnnotationParser->resolveAnnotationMethodCall($tokenIterator);

        $currentNode = $this->currentNodeProvider->getNode();
        if (! $currentNode instanceof Node) {
            throw new ShouldNotHappenException();
        }

        $fullyQualifiedAnnotationClass = $this->classAnnotationMatcher->resolveTagFullyQualifiedName(
            $annotationShortName,
            $currentNode
        );

        // keep the last ")"
        $tokenIterator->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        $tokenIterator->consumeTokenType(Lexer::TOKEN_CLOSE_PARENTHESES);

        $identifierTypeNode = new IdentifierTypeNode($fullyQualifiedAnnotationClass);
        return new DoctrineAnnotationTagValueNode($identifierTypeNode, $annotationShortName, $values);
    }
}
