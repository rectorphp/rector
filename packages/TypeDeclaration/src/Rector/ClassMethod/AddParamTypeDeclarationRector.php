<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Trait_;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\AddParamTypeDeclarationRectorTest
 */
final class AddParamTypeDeclarationRector extends AbstractRector
{
    /**
     * @var mixed[]
     */
    private $typehintForParameterByMethodByClass = [];

    /**
     * @param mixed[] $typehintForParameterByMethodByClass
     */
    public function __construct(array $typehintForParameterByMethodByClass = [])
    {
        $this->typehintForParameterByMethodByClass = $typehintForParameterByMethodByClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Add param types where needed', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass
{
    public function process($name)
    {
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    public function process(string $name)
    {
    }
}
PHP

            , [
                '$typehintForParameterByMethodByClass' => [
                    'SomeClass' => [
                        'process' => [
                            0 => 'string',
                        ],
                    ],
                ],
            ]),
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

        /** @var ClassLike $class */
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);

        foreach ($this->typehintForParameterByMethodByClass as $objectType => $typehintForParameterByMethod) {
            if (! $this->isObjectType($class, $objectType)) {
                continue;
            }

            foreach ($typehintForParameterByMethod as $methodName => $typehintByParameterPosition) {
                if (! $this->isName($node, $methodName)) {
                    continue;
                }

                $this->refactorClassMethodWithTypehintByParameterPosition($node, $typehintByParameterPosition);
            }
        }

        return $node;
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        // skip class methods without args
        if (count((array) $classMethod->params) === 0) {
            return true;
        }

        /** @var ClassLike|null $class */
        $class = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($class === null) {
            return true;
        }

        // skip traits
        if ($class instanceof Trait_) {
            return true;
        }

        // skip class without parents/interfaces
        if ($class instanceof Class_) {
            if ($class->implements) {
                return false;
            }

            if ($class->extends !== null) {
                return false;
            }

            return true;
        }

        // skip interface without parents
        /** @var Interface_ $class */
        return ! (bool) $class->extends;
    }

    private function refactorParameter(Param $param, string $newType): void
    {
        // already set → no change
        if ($param->type && $this->isName($param->type, $newType)) {
            return;
        }

        // remove it
        if ($newType === '') {
            $param->type = null;
            return;
        }

        $returnTypeNode = $this->staticTypeMapper->mapStringToPhpParserNode($newType);
        if ($returnTypeNode === null) {
            return;
        }

        $param->type = $returnTypeNode;
    }

    private function refactorClassMethodWithTypehintByParameterPosition(Node $node, $typehintByParameterPosition): void
    {
        foreach ($typehintByParameterPosition as $parameterPosition => $type) {
            if (! isset($node->params[$parameterPosition])) {
                continue;
            }

            $parameter = $node->params[$parameterPosition];
            $this->refactorParameter($parameter, $type);
        }
    }
}
