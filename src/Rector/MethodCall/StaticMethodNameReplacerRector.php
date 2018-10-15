<?php declare(strict_types=1);

namespace Rector\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Builder\IdentifierRenamer;
use Rector\NodeAnalyzer\MethodNameAnalyzer;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class StaticMethodNameReplacerRector extends AbstractRector
{
    /**
     * @var string[][]
     */
    private $perClassOldToNewMethods = [];

    /**
     * @var string[]
     */
    private $activeTypes = [];

    /**
     * @var MethodNameAnalyzer
     */
    private $methodNameAnalyzer;

    /**
     * @var IdentifierRenamer
     */
    private $identifierRenamer;

    /**
     * @param string[][] $perClassOldToNewMethods
     */
    public function __construct(
        array $perClassOldToNewMethods,
        MethodNameAnalyzer $methodNameAnalyzer,
        IdentifierRenamer $identifierRenamer
    ) {
        $this->perClassOldToNewMethods = $perClassOldToNewMethods;
        $this->methodNameAnalyzer = $methodNameAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns method names to new ones.', [
            new ConfiguredCodeSample(
                'SomeClass::oldStaticMethod();',
                'SomeClass::newStaticMethod();',
                [
                    '$perClassOldToNewMethods' => [
                        'SomeClass' => [
                            'oldMethod' => ['SomeClass', 'newMethod'],
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
        return [Identifier::class, StaticCall::class];
    }

    /**
     * @param Identifier|StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Identifier) {
            return $this->processIdentifierNode($node);
        }

        if ($node instanceof StaticCall) {
            return $this->processStaticCallNode($node);
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function getClasses(): array
    {
        return array_keys($this->perClassOldToNewMethods);
    }

    /**
     * @param mixed[] $oldToNewMethods
     */
    private function isClassRename(array $oldToNewMethods): bool
    {
        $firstMethodConfiguration = current($oldToNewMethods);

        return is_array($firstMethodConfiguration);
    }

    /**
     * @return string[]
     */
    private function matchOldToNewMethods(): array
    {
        foreach ($this->activeTypes as $activeType) {
            if (isset($this->perClassOldToNewMethods[$activeType])) {
                return $this->perClassOldToNewMethods[$activeType];
            }
        }

        return [];
    }

    /**
     * @param string[] $types
     */
    private function isMethodName(Node $node, array $types): bool
    {
        // already covered by previous methods
        if (! $node instanceof Identifier) {
            return false;
        }

        $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
        if ($parentNode instanceof StaticCall) {
            return false;
        }

        if (! $this->methodNameAnalyzer->isOverrideOfTypes($node, $types)) {
            return false;
        }

        /** @var Identifier $node */
        $parentClassName = $node->getAttribute(Attribute::PARENT_CLASS_NAME);

        /** @var Identifier $node */
        if (! isset($this->perClassOldToNewMethods[$parentClassName][$node->name])) {
            return false;
        }

        $this->activeTypes = [$parentClassName];

        return true;
    }

    private function resolveIdentifier(Identifier $node): Identifier
    {
        $oldToNewMethods = $this->matchOldToNewMethods();

        $methodName = $node->name;
        if (! isset($oldToNewMethods[$methodName])) {
            return $node;
        }

        $node->name = $oldToNewMethods[$methodName];

        return $node;
    }

    /**
     * @param string[] $oldToNewMethods
     */
    private function resolveClassRename(StaticCall $staticCallNode, array $oldToNewMethods, string $methodName): Node
    {
        [$newClass, $newMethod] = $oldToNewMethods[$methodName];

        $staticCallNode->class = new Name($newClass);
        $this->identifierRenamer->renameNode($staticCallNode, $newMethod);

        return $staticCallNode;
    }

    private function processIdentifierNode(Identifier $node): ?Identifier
    {
        $this->activeTypes = [];
        $matchedTypes = $this->matchTypes($node, $this->getClasses());
        if ($matchedTypes) {
            $this->activeTypes = $matchedTypes;
        }

        if ($this->isMethodName($node, $this->getClasses()) === false) {
            return null;
        }

        return $this->resolveIdentifier($node);
    }

    private function processStaticCallNode(StaticCall $node): ?Node
    {
        $this->activeTypes = [];
        $matchedTypes = $this->matchTypes($node, $this->getClasses());
        if ($matchedTypes) {
            $this->activeTypes = $matchedTypes;
        }

        $oldToNewMethods = $this->matchOldToNewMethods();

        /** @var Identifier $identifierNode */
        $identifierNode = $node->name;

        $methodName = $identifierNode->toString();
        if (! isset($oldToNewMethods[$methodName])) {
            return $node;
        }

        if ($this->isClassRename($oldToNewMethods)) {
            return $this->resolveClassRename($node, $oldToNewMethods, $methodName);
        }

        $this->identifierRenamer->renameNode($node, $oldToNewMethods[$methodName]);

        return $node;
    }
}
