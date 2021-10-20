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
use Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher;
use Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser;
use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
use Rector\Core\Configuration\CurrentNodeProvider;
use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix20211020\Symfony\Contracts\Service\Attribute\Required;
final class PlainValueParser
{
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser
     */
    private $staticDoctrineAnnotationParser;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\ArrayParser
     */
    private $arrayParser;
    /**
     * @var \Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher
     */
    private $classAnnotationMatcher;
    /**
     * @var \Rector\Core\Configuration\CurrentNodeProvider
     */
    private $currentNodeProvider;
    public function __construct(\Rector\BetterPhpDocParser\PhpDocParser\ClassAnnotationMatcher $classAnnotationMatcher, \Rector\Core\Configuration\CurrentNodeProvider $currentNodeProvider)
    {
        $this->classAnnotationMatcher = $classAnnotationMatcher;
        $this->currentNodeProvider = $currentNodeProvider;
    }
    /**
     * @required
     */
    public function autowirePlainValueParser(\Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser $staticDoctrineAnnotationParser, \Rector\BetterPhpDocParser\PhpDocParser\StaticDoctrineAnnotationParser\ArrayParser $arrayParser) : void
    {
        $this->staticDoctrineAnnotationParser = $staticDoctrineAnnotationParser;
        $this->arrayParser = $arrayParser;
    }
    /**
     * @return string|mixed[]|\PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode|\Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode
     */
    public function parseValue(\Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator $tokenIterator)
    {
        $currentTokenValue = $tokenIterator->currentTokenValue();
        // temporary hackaround multi-line doctrine annotations
        if ($tokenIterator->isCurrentTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_END)) {
            return $currentTokenValue;
        }
        // consume the token
        $isOpenCurlyArray = $tokenIterator->isCurrentTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_OPEN_CURLY_BRACKET);
        if ($isOpenCurlyArray) {
            return $this->arrayParser->parseCurlyArray($tokenIterator);
        }
        $tokenIterator->next();
        // normalize value
        $constantValue = $this->matchConstantValue($currentTokenValue);
        if ($constantValue !== null) {
            return $constantValue;
        }
        while ($tokenIterator->isCurrentTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_DOUBLE_COLON) || $tokenIterator->isCurrentTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_IDENTIFIER)) {
            $currentTokenValue .= $tokenIterator->currentTokenValue();
            $tokenIterator->next();
        }
        // nested entity!
        if ($tokenIterator->isCurrentTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_OPEN_PARENTHESES)) {
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
            return $tokenIterator->printFromTo($start, $end);
        }
        return $currentTokenValue;
    }
    private function parseNestedDoctrineAnnotationTagValueNode(string $currentTokenValue, \Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator $tokenIterator) : \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode
    {
        // @todo
        $annotationShortName = $currentTokenValue;
        $values = $this->staticDoctrineAnnotationParser->resolveAnnotationMethodCall($tokenIterator);
        $currentNode = $this->currentNodeProvider->getNode();
        if (!$currentNode instanceof \PhpParser\Node) {
            throw new \Rector\Core\Exception\ShouldNotHappenException();
        }
        $fullyQualifiedAnnotationClass = $this->classAnnotationMatcher->resolveTagFullyQualifiedName($annotationShortName, $currentNode);
        // keep the last ")"
        $tokenIterator->tryConsumeTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_PHPDOC_EOL);
        $tokenIterator->consumeTokenType(\PHPStan\PhpDocParser\Lexer\Lexer::TOKEN_CLOSE_PARENTHESES);
        // keep original name to differentiate between short and FQN class
        $identifierTypeNode = new \PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode($annotationShortName);
        $identifierTypeNode->setAttribute(\Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey::RESOLVED_CLASS, $fullyQualifiedAnnotationClass);
        return new \Rector\BetterPhpDocParser\PhpDoc\DoctrineAnnotationTagValueNode($identifierTypeNode, $annotationShortName, $values);
    }
    /**
     * @return \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprNode|null
     */
    private function matchConstantValue(string $currentTokenValue)
    {
        if (\strtolower($currentTokenValue) === 'false') {
            return new \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprFalseNode();
        }
        if (\strtolower($currentTokenValue) === 'true') {
            return new \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprTrueNode();
        }
        if (!\is_numeric($currentTokenValue)) {
            return null;
        }
        if ((string) (int) $currentTokenValue !== $currentTokenValue) {
            return null;
        }
        return new \PHPStan\PhpDocParser\Ast\ConstExpr\ConstExprIntegerNode($currentTokenValue);
    }
}
