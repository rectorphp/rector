<?php

declare(strict_types=1);

namespace Rector\PHPStanExtensions\Rule;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use PhpParser\NodeFinder;
use PHPStan\Analyser\Scope;
use PHPStan\Rules\Rule;

/**
 * @see \Rector\PHPStanExtensions\Tests\Rule\RectorRuleAndValueObjectHaveSameStartsRule\RectorRuleAndValueObjectHaveSameStartsRuleTest
 */
final class RectorRuleAndValueObjectHaveSameStartsRule implements Rule
{
    /**
     * @var string
     */
    public const ERROR = 'Value Object class name "%s" is incorrect. The correct class name is "%s".';

    public function getNodeType(): string
    {
        return MethodCall::class;
    }

    /**
     * @param MethodCall $node
     * @return string[]
     */
    public function processNode(Node $node, Scope $scope): array
    {
        if ($this->shouldSkip($node)) {
            return [];
        }

        $valueObjectClassName = $this->getValueObjectClassName($node);
        if ($valueObjectClassName === null) {
            return [];
        }

        $rectorRuleClassName = $this->getRectorRuleClassName($node);
        if ($rectorRuleClassName === null) {
            return [];
        }

        if ($rectorRuleClassName === $valueObjectClassName . 'Rector') {
            return [];
        }

        return [sprintf(self::ERROR, $valueObjectClassName, rtrim($rectorRuleClassName, 'Rector'))];
    }

    private function shouldSkip(MethodCall $node): bool
    {
        /** @var Identifier $name */
        $name = $node->name;
        if ($name->toString() !== 'call') {
            return true;
        }
        /** @var String_ $expr */
        $expr = $node->args[0]->value;
        return $expr->value !== 'configure';
    }

    private function getValueObjectClassName(MethodCall $methodCall): ?string
    {
        $nodeFinder = new NodeFinder();
        $inlineValueObjectsNode = $nodeFinder->findFirst($methodCall->args[1], function (Node $node): ?FuncCall {
            if (! $node instanceof FuncCall) {
                return null;
            }

            $className = $this->getClassNameFromNode($node);
            if ($className === null) {
                return null;
            }

            if ($className !== 'inline_value_objects') {
                return null;
            }

            return $node;
        });

        if ($inlineValueObjectsNode === null) {
            return null;
        }

        $valueObjectNode = $nodeFinder->findFirstInstanceOf($inlineValueObjectsNode, New_::class);
        if ($valueObjectNode === null) {
            return null;
        }

        return $this->getClassNameFromNode($valueObjectNode);
    }

    private function getRectorRuleClassName(Node $node): ?string
    {
        /** @var ClassConstFetch $classConstFetch */
        $classConstFetch = $node->var->args[0]->value;
        return $this->getClassNameFromNode($classConstFetch);
    }

    private function getClassNameFromNode(Node $node): ?string
    {
        $parts = [];
        if ($node instanceof New_ || $node instanceof ClassConstFetch) {
            /** @var FullyQualified $classNode */
            $classNode = $node->class;
            $parts = $classNode->parts;
        }

        if ($node instanceof FuncCall) {
            /** @var FullyQualified $nameNode */
            $nameNode = $node->name;
            $parts = $nameNode->parts;
        }

        $className = end($parts);
        if ($className === false) {
            return null;
        }

        return $className;
    }
}
