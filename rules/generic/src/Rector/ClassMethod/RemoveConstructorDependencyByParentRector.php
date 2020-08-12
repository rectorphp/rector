<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Core\ValueObject\MethodName;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @deprecated Very limited in removal, not covered with edge-cases.
 *
 * @see \Rector\Generic\Tests\Rector\ClassMethod\RemoveConstructorDependencyByParentRector\RemoveConstructorDependencyByParentRectorTest
 */
final class RemoveConstructorDependencyByParentRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     * @api
     */
    public const PARENT_TYPE_TO_PARAM_TYPES_TO_REMOVE = '$parentsDependenciesToRemove';

    /**
     * @var array<string, string[]>
     */
    private $parentsDependenciesToRemove = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Removes params in constructor by parent type and param names', [
            new ConfiguredCodeSample(
                    <<<'PHP'
class SomeClass extends SomeParentClass
{
    public function __construct(SomeType $someType)
    {
    }
}
PHP
                    ,
                    <<<'PHP'
class SomeClass extends SomeParentClass
{
    public function __construct()
    {
    }
}
PHP
                    , [
                        self::PARENT_TYPE_TO_PARAM_TYPES_TO_REMOVE => [
                            'SomeParentClass' => ['someType'],
                        ],
                    ]
                ),
        ]);
    }

    /**
     * @return class-string[]
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
        $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
        if ($parentClassName === null) {
            return null;
        }

        if (! $this->isName($node, MethodName::CONSTRUCT)) {
            return null;
        }

        foreach ($this->parentsDependenciesToRemove as $parentClass => $paramsToRemove) {
            $parentClassName = $node->getAttribute(AttributeKey::PARENT_CLASS_NAME);
            if ($parentClassName !== $parentClass) {
                continue;
            }

            $this->removeClassMethodParameterByName($node, $paramsToRemove);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->parentsDependenciesToRemove = $configuration[self::PARENT_TYPE_TO_PARAM_TYPES_TO_REMOVE] ?? [];
    }

    /**
     * @param string[] $paramNamesToRemove
     */
    private function removeClassMethodParameterByName(ClassMethod $classMethod, array $paramNamesToRemove): void
    {
        foreach ($paramNamesToRemove as $paramNameToRemove) {
            foreach ($classMethod->params as $param) {
                if ($param->type === null) {
                    continue;
                }

                if (! $this->isName($param->type, $paramNameToRemove)) {
                    continue;
                }

                $this->removeNode($param);
            }
        }
    }
}
