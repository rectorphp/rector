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
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddParamTypeDeclarationRector\AddParamTypeDeclarationRectorTest
 */
final class AddParamTypeDeclarationRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const TYPEHINT_FOR_PARAMETER_BY_METHOD_BY_CLASS = '$typehintForParameterByMethodByClass';

    /**
     * @var mixed[]
     */
    private $typehintForParameterByMethodByClass = [];

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

        /** @var ClassLike $classLike */
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);

        foreach ($this->typehintForParameterByMethodByClass as $objectType => $typehintForParameterByMethod) {
            if (! $this->isObjectType($classLike, $objectType)) {
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

    public function configure(array $configuration): void
    {
        $this->typehintForParameterByMethodByClass = $configuration[self::TYPEHINT_FOR_PARAMETER_BY_METHOD_BY_CLASS] ?? [];
    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        // skip class methods without args
        if (count((array) $classMethod->params) === 0) {
            return true;
        }

        /** @var ClassLike|null $classLike */
        $classLike = $classMethod->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return true;
        }

        // skip traits
        if ($classLike instanceof Trait_) {
            return true;
        }

        // skip class without parents/interfaces
        if ($classLike instanceof Class_) {
            if ($classLike->implements !== []) {
                return false;
            }

            if ($classLike->extends !== null) {
                return false;
            }

            return true;
        }

        // skip interface without parents
        /** @var Interface_ $classLike */
        return ! (bool) $classLike->extends;
    }

    private function refactorClassMethodWithTypehintByParameterPosition(
        ClassMethod $classMethod,
        array $typehintByParameterPosition
    ): void {
        foreach ($typehintByParameterPosition as $parameterPosition => $type) {
            if (! isset($classMethod->params[$parameterPosition])) {
                continue;
            }

            $parameter = $classMethod->params[$parameterPosition];
            $this->refactorParameter($parameter, $type);
        }
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
        $param->type = $returnTypeNode;
    }
}
