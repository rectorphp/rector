<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ArrayType;
use PHPStan\Type\MixedType;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfo;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Generic\Tests\Rector\ClassMethod\ChangeContractMethodSingleToManyRector\ChangeContractMethodSingleToManyRectorTest
 */
final class ChangeContractMethodSingleToManyRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @api
     * @var string
     */
    public const OLD_TO_NEW_METHOD_BY_TYPE = '$oldToNewMethodByType';

    /**
     * @var mixed[]
     */
    private $oldToNewMethodByType = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change method that returns single value to multiple values', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
class SomeClass
{
    public function getNode(): string
    {
        return 'Echo_';
    }
}
CODE_SAMPLE
,
                <<<'CODE_SAMPLE'
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
CODE_SAMPLE
            , [
                self::OLD_TO_NEW_METHOD_BY_TYPE => [
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
        /** @var Class_ $classLike */
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);

        /** @var string $type */
        foreach ($this->oldToNewMethodByType as $type => $oldToNewMethod) {
            if (! $this->isObjectType($classLike, $type)) {
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

    public function configure(array $configuration): void
    {
        $this->oldToNewMethodByType = $configuration[self::OLD_TO_NEW_METHOD_BY_TYPE] ?? [];
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
}
