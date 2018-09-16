<?php declare(strict_types=1);

namespace Rector\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use Rector\Builder\IdentifierRenamer;
use Rector\NodeAnalyzer\MethodCallAnalyzer;
use Rector\NodeAnalyzer\MethodNameAnalyzer;
use Rector\NodeTypeResolver\Node\Attribute;
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
        MethodNameAnalyzer $methodNameAnalyzer,
        IdentifierRenamer $identifierRenamer
    ) {
        $this->perClassOldToNewMethods = $perClassOldToNewMethods;
        $this->methodCallAnalyzer = $methodCallAnalyzer;
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
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Identifier::class, MethodCall::class];
    }

    /**
     * @param Identifier|MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof Identifier) {
            return $this->processIdentifierNode($node);
        }

        if ($node instanceof MethodCall) {
            return $this->processMethodCall($node);
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
        if ($parentNode instanceof MethodCall) {
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

    private function processIdentifierNode(Identifier $identifierNode): ?Identifier
    {
        $this->activeTypes = [];

        $matchedTypes = $this->methodCallAnalyzer->matchTypes($identifierNode, $this->getClasses());

        if ($matchedTypes) {
            $this->activeTypes = $matchedTypes;
        }

        if ($this->isMethodName($identifierNode, $this->getClasses()) === false) {
            return null;
        }

        return $this->resolveIdentifier($identifierNode);
    }

    private function processMethodCall(MethodCall $node): ?MethodCall
    {
        $this->activeTypes = [];
        $matchedTypes = $this->methodCallAnalyzer->matchTypes($node, $this->getClasses());
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

        $this->identifierRenamer->renameNode($node, $oldToNewMethods[$methodName]);

        return $node;
    }
}
