<?php

declare(strict_types=1);

namespace Rector\Core\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Core\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector\ChangeContractMethodSingleToManyRectorTest
 */
final class ChangeContractMethodSingleToManyRector extends AbstractRector
{
    /**
     * @var mixed[]
     */
    private $oldToNewMethodByType = [];

    /**
     * E.g.:
     * ClassType => [
     *     oldMethod => newMethod
     * ]
     */
    public function __construct(array $oldToNewMethodByType = [])
    {
        $this->oldToNewMethodByType = $oldToNewMethodByType;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change method that returns single value to multiple values', [
            new ConfiguredCodeSample(
                <<<'PHP'
class SomeClass
{
    public function getNode(): string
    {
        return 'Echo_';
    }
}
PHP
,
                <<<'PHP'
class SomeClass
{
    /**
     * @return string[]
     */
    public function getNodes(): array
    {
        return ['Echo_'];
    }
}
PHP
            , [
                '$oldToNewMethodByType' => [
                    'SomeClass' => [
                        'getNode' => 'getNodes',
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
        /** @var Class_ $class */
        $class = $node->getAttribute(AttributeKey::CLASS_NODE);

        /** @var string $type */
        foreach ($this->oldToNewMethodByType as $type => $oldToNewMethod) {
            if (! $this->isObjectType($class, $type)) {
                continue;
            }

            foreach ($oldToNewMethod as $oldMethod => $newMethod) {
                if (! $this->isName($node, $oldMethod)) {
                    continue;
                }

                $node->name = new Identifier($newMethod);
                $this->keepOldReturnTypeInDocBlock($node);

                $node->returnType = new Identifier('array');
                $this->wrapReturnValueToArray($node);

                break;
            }
        }

        return $node;
    }

    private function wrapReturnValueToArray(ClassMethod $classMethod): void
    {
        $this->traverseNodesWithCallable((array) $classMethod->stmts, function (Node $node) {
            if (! $node instanceof Return_) {
                return null;
            }

            $node->expr = $this->createArray([$node->expr]);
            return null;
        });
    }

    private function keepOldReturnTypeInDocBlock(ClassMethod $classMethod): void
    {
        // keep old return type in the docblock
        $oldReturnType = $classMethod->returnType;
        if ($oldReturnType === null) {
            return;
        }

        $staticType = $this->staticTypeMapper->mapPhpParserNodePHPStanType($oldReturnType);
        $arrayType = new ArrayType(new MixedType(), $staticType);

        /** @var PhpDocInfo $phpDocInfo */
        $phpDocInfo = $classMethod->getAttribute(AttributeKey::PHP_DOC_INFO);
        $phpDocInfo->changeReturnType($arrayType);
    }
}
