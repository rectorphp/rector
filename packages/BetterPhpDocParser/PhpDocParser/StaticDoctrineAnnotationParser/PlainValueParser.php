<?php

declare (strict_types=1);
namespace Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser;

use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode;
use PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Lexer\Lexer;
use Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode;
use Rector\BetterPhpDocParser\PhpDoc\StringNode;
use Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix202308\Symfony\Contracts\Service\Attribute\Required;
final class PlainValueParser
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher
     */
    private $classAnnotationMatcher;
    /**
     * @readonly
     * @var \Rector\Core\Configuration\CurrentNodeProvider
     */
    private $currentNodeProvider;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser
     */
    private $staticDoctrineAnnotationParser;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\ArrayParser
     */
    private $arrayParser;
    public function __construct(ClassAnnotationMatcher $classAnnotationMatcher, CurrentNodeProvider $currentNodeProvider)
    {
        $this->classAnnotationMatcher = $classAnnotationMatcher;
        $this->currentNodeProvider = $currentNodeProvider;
    }
    /**
     * @required
     */
    public function autowire(StaticDoctrineAnnotationParser $staticDoctrineAnnotationParser, \Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\ArrayParser $arrayParser) : void
    {
        $this->staticDoctrineAnnotationParser = $staticDoctrineAnnotationParser;
        $this->arrayParser = $arrayParser;
    }
    /**
     * @return string|mixed[]|ConstExprNode|DoctrineAnnotationTagValueNode|StringNode
     */
    public function parseValue(BetterTokenIterator $tokenIterator)
    {
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
        $constExprNode = $this->matchConstantValue($currentTokenValue);
        if ($constExprNode instanceof ConstExprNode) {
            return $constExprNode;
        }
        while ($tokenIterator->isCurrentTokenType(Lexer::TOKEN_DOUBLE_COLON) || $tokenIterator->isCurrentTokenType(Lexer::TOKEN_IDENTIFIER)) {
            $currentTokenValue .= $tokenIterator->currentTokenValue();
            $tokenIterator->next();
        }
        // nested entity!, supported in attribute since PHP 8.1
        if ($tokenIterator->isCurrentTokenType(Lexer::TOKEN_OPEN_PARENTHESES)) {
            return $this->parseNestedDoctrineAnnotationTagValueNode($currentTokenValue, $tokenIterator);
        }
        $start = $tokenIterator->currentPosition();
        // from "quote to quote"
        if ($currentTokenValue === '"') {
            do {
                $tokenIterator->next();
            } while (\strpos($tokenIterator->currentTokenValue(), '"') === \false);
        }
        $end = $tokenIterator->currentPosition();
        if ($start + 1 < $end) {
            return new StringNode($tokenIterator->printFromTo($start, $end));
        }
        return $currentTokenValue;
    }
    private function parseNestedDoctrineAnnotationTagValueNode(string $currentTokenValue, BetterTokenIterator $tokenIterator) : DoctrineAnnotationTagValueNode
    {
        // @todo
        $annotationShortName = $currentTokenValue;
        $values = $this->staticDoctrineAnnotationParser->resolveAnnotationMethodCall($tokenIterator);
        $currentNode = $this->currentNodeProvider->getNode();
        if (!$currentNode instanceof Node) {
            throw new ShouldNotHappenException();
        }
        $fullyQualifiedAnnotationClass = $this->classAnnotationMatcher->resolveTagFullyQualifiedName($annotationShortName, $currentNode);
        // keep the last ")"
        $tokenIterator->tryConsumeTokenType(Lexer::TOKEN_PHPDOC_EOL);
        if ($tokenIterator->currentTokenValue() === ')') {
            $tokenIterator->consumeTokenType(Lexer::TOKEN_CLOSE_PARENTHESES);
        }
        // keep original name to differentiate between short and FQN class
        $identifierTypeNode = new IdentifierTypeNode($annotationShortName);
        $identifierTypeNode->setAttribute(PhpDocAttributeKey::RESOLVED_CLASS, $fullyQualifiedAnnotationClass);
        return new DoctrineAnnotationTagValueNode($identifierTypeNode, $annotationShortName, $values);
    }
    private function matchConstantValue(string $currentTokenValue) : ?\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode
    {
        if (\strtolower($currentTokenValue) === 'false') {
            return new ConstExprFalseNode();
        }
        if (\strtolower($currentTokenValue) === 'true') {
            return new ConstExprTrueNode();
        }
        if (!\is_numeric($currentTokenValue)) {
            return null;
        }
        if ((string) (int) $currentTokenValue !== $currentTokenValue) {
            return null;
        }
        return new ConstExprIntegerNode($currentTokenValue);
    }
}
