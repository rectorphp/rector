<?php declare(strict_types=1);

namespace Rector\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use Rector\Builder\IdentifierRenamer;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeAnalyzer\MethodNameAnalyzer;
use Rector\NodeAnalyzer\StaticMethodCallAnalyzer;
use Rector\NodeTypeResolver\Node\MetadataAttribute;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\ConfiguredCodeSample;
use Rector\RectorDefinition\RectorDefinition;
use SomeClass;

final class MethodNameReplacerRector extends AbstractRector
{
    /**
     * class => [
     *     oldMethod => newMethod
     * ]
     *
     * or (typically for static calls):
     *
     * class => [
     *     oldMethod => [
     *          newClass, newMethod
     *     ]
     * ]
     *
     * @todo consider splitting to static call replacer or class rename,
     * this api can lead users to bugs (already did)
     *
     * @var string[][]
     */
    private $perClassOldToNewMethods = [];

    /**
     * @var string[]
     */
    private $activeTypes = [];

    /**
     * @var MethodCallAnalyzer
     */
    private $methodCallAnalyzer;

    /**
     * @var StaticMethodCallAnalyzer
     */
    private $staticMethodCallAnalyzer;

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
        MethodCallAnalyzer $methodCallAnalyzer,
        StaticMethodCallAnalyzer $staticMethodCallAnalyzer,
        MethodNameAnalyzer $methodNameAnalyzer,
        IdentifierRenamer $identifierRenamer
    ) {
        $this->perClassOldToNewMethods = $perClassOldToNewMethods;
        $this->methodCallAnalyzer = $methodCallAnalyzer;
        $this->staticMethodCallAnalyzer = $staticMethodCallAnalyzer;
        $this->methodNameAnalyzer = $methodNameAnalyzer;
        $this->identifierRenamer = $identifierRenamer;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Turns method names to new ones.', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->oldMethod();
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->newMethod();
CODE_SAMPLE
                ,
                [
                    '$perClassOldToNewMethods' => [
                        SomeClass::class => [
                            'oldMethod' => 'newMethod',
                        ],
                    ],
                ]
            ),
            new ConfiguredCodeSample(
                'SomeClass::oldStaticMethod();',
                'SomeClass::newStaticMethod();',
                [
                    '$perClassOldToNewMethods' => [
                        SomeClass::class => [
                            'oldMethod' => [SomeClass::class, 'newMethod'],
                        ],
                    ],
                ]
            ),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        $this->activeTypes = [];

        $matchedTypes = $this->methodCallAnalyzer->matchTypes($node, $this->getClasses());
        if ($matchedTypes) {
            $this->activeTypes = $matchedTypes;

            return true;
        }

        $matchedTypes = $this->staticMethodCallAnalyzer->matchTypes($node, $this->getClasses());
        if ($matchedTypes) {
            $this->activeTypes = $matchedTypes;

            return true;
        }

        return $this->isMethodName($node, $this->getClasses());
    }

    /**
     * @param Identifier|StaticCall|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Identifier) {
            return $this->resolveIdentifier($node);
        }

        $oldToNewMethods = $this->matchOldToNewMethods();

        /** @var Identifier $identifierNode */
        $identifierNode = $node->name;

        $methodName = $identifierNode->toString();
        if (! isset($oldToNewMethods[$methodName])) {
            return $node;
        }

        if ($node instanceof StaticCall && $this->isClassRename($oldToNewMethods)) {
            return $this->resolveClassRename($node, $oldToNewMethods, $methodName);
        }

        $this->identifierRenamer->renameNode($node, $oldToNewMethods[$methodName]);

        return $node;
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
            if ($this->perClassOldToNewMethods[$activeType]) {
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
        if ($parentNode instanceof MethodCall || $parentNode instanceof StaticCall) {
            return false;
        }

        if (! $this->methodNameAnalyzer->isOverrideOfTypes($node, $types)) {
            return false;
        }

        /** @var Identifier $node */
        $parentClassName = $node->getAttribute(MetadataAttribute::PARENT_CLASS_NAME);

        /** @var Identifier $node */
        if (! isset($this->perClassOldToNewMethods[$parentClassName][$node->name])) {
            return false;
        }

        $this->activeTypes = [$parentClassName];

        return true;
    }

    private function resolveIdentifier(Identifier $node): Node
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
}
