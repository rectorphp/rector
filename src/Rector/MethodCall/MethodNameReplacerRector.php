<?php declare(strict_types=1);

namespace Rector\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class MethodNameReplacerRector extends AbstractRector
{
    /**
     * class => [
     *     oldMethod => newMethod
     * ]
     *
     * @var string[][]
     */
    private $oldToNewMethodsByClass = [];

    /**
     * @param string[][] $oldToNewMethodsByClass
     */
    public function __construct(array $oldToNewMethodsByClass)
    {
        $this->oldToNewMethodsByClass = $oldToNewMethodsByClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns method names to new ones.', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->oldMethod();
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->newMethod();
CODE_SAMPLE
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
        return [Identifier::class];
    }

    /**
     * @param Identifier $node
     */
    public function refactor(Node $node): ?Node
    {
        $parentNode = $node->getAttribute(Attribute::PARENT_NODE);

        foreach ($this->oldToNewMethodsByClass as $type => $oldToNewMethods) {
            if (! $this->isType($parentNode, $type)) {
                continue;
            }

            foreach ($oldToNewMethods as $oldMethod => $newMethod) {
                if (! $this->isNameInsensitive($node, $oldMethod)) {
                    continue;
                }

                $node->name = $newMethod;
                return $node;
            }
        }

        return $node;
    }
}
