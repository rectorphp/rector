<?php

declare(strict_types=1);

namespace Rector\Order\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Order\Tests\Rector\ClassMethod\OrderConstructorDependenciesByTypeAlphabeticallyRector\OrderConstructorDependenciesByTypeAlphabeticallyRectorTest
 */
final class OrderConstructorDependenciesByTypeAlphabeticallyRector extends AbstractRector
{
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Order __constructor dependencies by type A-Z', [
            new CodeSample(
                <<<'PHP'
class SomeClass
{
    public function __construct(
        LatteToTwigConverter $latteToTwigConverter,
        SymfonyStyle $symfonyStyle,
        LatteAndTwigFinder $latteAndTwigFinder,
        SmartFileSystem $smartFileSystem,
    ) {
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function __construct(
        LatteAndTwigFinder $latteAndTwigFinder,
        LatteToTwigConverter $latteToTwigConverter,
        SmartFileSystem $smartFileSystem,
        SymfonyStyle $symfonyStyle,
    ) {
    }
}
PHP

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $node->params = $this->getSortedParams($node);

        return $node;
    }

    /**
     * @return Param[]
     */
    private function getSortedParams(ClassMethod $classMethod): array
    {
        $params = $classMethod->getParams();
        usort($params, function (Param $firstParam, Param $secondParam) {
            /** @var Name $typeA */
            $typeA = $firstParam->type;
            /** @var Name $typeB */
            $typeB = $secondParam->type;
            return strcmp($this->getShortName($typeA), $this->getShortName($typeB));
        });

        return $params;
    }

    private function isPrimitiveDataTypeParam(Param $param): bool
    {
        return $param->type instanceof Identifier;
    }

    private function isDefaultValueParam(Param $param): bool
    {
        return $param->default instanceof Expr || $param->type instanceof NullableType;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        if (! $this->isName($classMethod, '__construct')) {
            return true;
        }

        if ($classMethod->params === []) {
            return true;
        }

        foreach ($classMethod->params as $param) {
            if ($this->isDefaultValueParam($param)) {
                return true;
            }

            if ($this->isPrimitiveDataTypeParam($param)) {
                return true;
            }
        }

        return false;
    }
}
