<?php

declare(strict_types=1);

namespace Rector\Renaming\Rector\MethodCall;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

/**
 * @see \Rector\Renaming\Tests\Rector\MethodCall\RenameMethodCallRector\RenameMethodCallRectorTest
 */
final class RenameMethodCallRector extends AbstractRector
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
        return new RectorDefinition('Turns method call names to new ones.', [
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
                        'oldMethod' => 'newMethod',
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
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->oldToNewMethodsByClass as $type => $oldToNewMethods) {
            if (! $this->isObjectType($node->var, $type)) {
                continue;
            }

            foreach ($oldToNewMethods as $oldMethod => $newMethod) {
                if (! $this->isName($node->name, $oldMethod)) {
                    continue;
                }

                $newNode = $this->process($node, $newMethod);
                if ($newNode !== null) {
                    return $newNode;
                }
            }
        }

        return null;
    }

    /**
     * @param MethodCall $node
     * @param string|mixed[] $newMethod
     * @return MethodCall|ArrayDimFetch
     */
    private function process(Node $node, $newMethod): Node
    {
        if (is_string($newMethod)) {
            $node->name = new Identifier($newMethod);

            return $node;
        }

        // special case for array dim fetch
        $node->name = new Identifier($newMethod['name']);

        return new ArrayDimFetch($node, BuilderHelpers::normalizeValue($newMethod['array_key']));
    }
}
