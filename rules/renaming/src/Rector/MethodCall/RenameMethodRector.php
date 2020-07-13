<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\MethodCall;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see \Rector\Renaming\Tests\Rector\MethodCall\RenameMethodRector\RenameMethodRectorTest
 */
final class RenameMethodRector extends AbstractRector
{
    /**
     * class => [
     *     oldMethod => newMethod
     * ]
     *
     * @var string[][]|mixed[][][]
     */
    private $oldToNewMethodsByClass = [];

    /**
     * @param string[][]|mixed[][][] $oldToNewMethodsByClass
     */
    public function __construct(array $oldToNewMethodsByClass = [])
    {
        $this->oldToNewMethodsByClass = $oldToNewMethodsByClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns method names to new ones.', [
            new ConfiguredCodeSample(
                <<<'PHP'
$someObject = new SomeExampleClass;
$someObject->oldMethod();
PHP
                ,
                <<<'PHP'
$someObject = new SomeExampleClass;
$someObject->newMethod();
PHP
                ,
                [
                    'SomeExampleClass' => [
                        '$oldToNewMethodsByClass' => [
                            'oldMethod' => 'newMethod',
                        ],
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class, StaticCall::class, ClassMethod::class];
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->oldToNewMethodsByClass as $type => $oldToNewMethods) {
            if (! $this->isMethodStaticCallOrClassMethodObjectType($node, $type)) {
                continue;
            }

            foreach ($oldToNewMethods as $oldMethod => $newMethod) {
                if (! $this->isName($node->name, $oldMethod)) {
                    continue;
                }

                if ($this->shouldSkipForAlreadyExistingClassMethod($node, $newMethod)) {
                    continue;
                }

                $newNode = $this->renameToMethod($node, $newMethod);
                if ($newNode !== null) {
                    return $newNode;
                }
            }
        }

        return null;
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     * @param string|mixed[] $newMethod
     */
    private function renameToMethod(Node $node, $newMethod): ?Node
    {
        if (is_string($newMethod)) {
            $node->name = new Identifier($newMethod);

            return $node;
        }

        // special case for array dim fetch
        if (! $node instanceof ClassMethod) {
            $node->name = new Identifier($newMethod['name']);

            return new ArrayDimFetch($node, BuilderHelpers::normalizeValue($newMethod['array_key']));
        }

        return null;
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     * @param string|mixed[] $newMethod
     */
    private function shouldSkipForAlreadyExistingClassMethod(Node $node, $newMethod): bool
    {
        if (! is_string($newMethod)) {
            return false;
        }

        if (! $node instanceof ClassMethod) {
            return false;
        }

        /** @var ClassLike|null $classLike */
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return false;
        }

        return (bool) $classLike->getMethod($newMethod);
    }
}
