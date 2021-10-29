<?php

declare(strict_types=1);

namespace Rector\CodeQuality\Rector\Name;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\UseUse;
use PhpParser\Node\UnionType;
use PHPStan\Reflection\ReflectionProvider;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * Mostly mimics source from
 * @changelog https://github.com/phpstan/phpstan-src/blob/master/src/Rules/ClassCaseSensitivityCheck.php
 *
 * @see \Rector\Tests\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector\FixClassCaseSensitivityNameRectorTest
 */
final class FixClassCaseSensitivityNameRector extends AbstractRector
{
    public function __construct(
        private ReflectionProvider $reflectionProvider
    ) {
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change miss-typed case sensitivity name to correct one',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $anotherClass = new anotherclass;
    }
}

final class AnotherClass
{
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
final class SomeClass
{
    public function run()
    {
        $anotherClass = new AnotherClass;
    }
}

final class AnotherClass
{
}
CODE_SAMPLE
                ),
            ]
        );
    }

    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Name::class];
    }

    /**
     * @param Name $node
     */
    public function refactor(Node $node): ?Node
    {
        $fullyQualifiedName = $this->resolveFullyQualifiedName($node);

        if (! $this->reflectionProvider->hasClass($fullyQualifiedName)) {
            return null;
        }

        $classReflection = $this->reflectionProvider->getClass($fullyQualifiedName);
        if ($classReflection->isBuiltin()) {
            // skip built-in classes
            return null;
        }

        $realClassName = $classReflection->getName();
        if (strtolower($realClassName) !== strtolower($fullyQualifiedName)) {
            // skip class alias
            return null;
        }

        if ($realClassName === $fullyQualifiedName) {
            return null;
        }

        $scope = $node->getAttribute(AttributeKey::SCOPE);
        $hasFunction = $this->reflectionProvider->hasFunction(new FullyQualified($fullyQualifiedName), $scope);
        if ($hasFunction) {
            return null;
        }

        $parent = $node->getAttribute(AttributeKey::PARENT_NODE);

        // do not FQN use imports
        if ($parent instanceof UseUse) {
            return new Name($realClassName);
        }

        return new FullyQualified($realClassName);
    }

    private function resolveFullyQualifiedName(Name $name): string
    {
        $parent = $name->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Node) {
            return '';
        }

        $originalName = $name->getAttribute(AttributeKey::ORIGINAL_NAME);
        if (! $originalName instanceof Name) {
            return $this->getName($name);
        }

        if ($this->isParamTypeNameOrClassConstFetchClassName($parent)) {
            return $this->processParamTypeNameOrClassConstFetchClassName($name, $originalName);
        }

        if ($parent instanceof UnionType) {
            return $this->processUnionType($parent, $name, $originalName);
        }

        // replace parts from the old one
        $originalReversedParts = array_reverse($originalName->parts);
        $resolvedReversedParts = array_reverse($name->parts);

        $mergedReversedParts = $originalReversedParts + $resolvedReversedParts;
        $mergedParts = array_reverse($mergedReversedParts);

        return implode('\\', $mergedParts);
    }

    private function isParamTypeNameOrClassConstFetchClassName(Node $node): bool
    {
        if (! $node instanceof Param && ! $node instanceof ClassConstFetch) {
            return false;
        }

        if ($node instanceof Param) {
            return $node->type instanceof Name;
        }

        return $node->class instanceof Name;
    }

    private function processUnionType(UnionType $unionType, Name $name, Name $originalName): string
    {
        foreach ($unionType->types as $type) {
            if (! $type instanceof Name) {
                continue;
            }

            if ($type !== $name) {
                continue;
            }

            return $this->processParamTypeNameOrClassConstFetchClassName($name, $originalName);
        }

        return '';
    }

    private function processParamTypeNameOrClassConstFetchClassName(Name $name, Name $originalName): string
    {
        $oldTokens = $this->file->getOldTokens();
        $startTokenPos = $name->getStartTokenPos();

        if (! isset($oldTokens[$startTokenPos][1])) {
            return '';
        }

        $type = $oldTokens[$startTokenPos][1];
        if (str_contains($type, '\\')) {
            return '';
        }

        $last = $originalName->getLast();
        if (strtolower($last) !== strtolower($type)) {
            return '';
        }

        $name->parts[count($name->parts) - 1] = $type;
        return (string) $name;
    }
}
