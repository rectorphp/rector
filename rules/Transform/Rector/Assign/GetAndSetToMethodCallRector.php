<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Transform\Rector\Assign;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use RectorPrefix20220606\Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use RectorPrefix20220606\Rector\Core\NodeManipulator\MagicPropertyFetchAnalyzer;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Rector\Transform\ValueObject\GetAndSetToMethodCall;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220606\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\Assign\GetAndSetToMethodCallRector\GetAndSetToMethodCallRectorTest
 */
final class GetAndSetToMethodCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var GetAndSetToMethodCall[]
     */
    private $getAndSetToMethodCalls = [];
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer
     */
    private $propertyFetchAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\MagicPropertyFetchAnalyzer
     */
    private $magicPropertyFetchAnalyzer;
    public function __construct(PropertyFetchAnalyzer $propertyFetchAnalyzer, MagicPropertyFetchAnalyzer $magicPropertyFetchAnalyzer)
    {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->magicPropertyFetchAnalyzer = $magicPropertyFetchAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Turns defined `__get`/`__set` to specific method calls.', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$container = new SomeContainer;
$container->someService = $someService;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$container = new SomeContainer;
$container->setService("someService", $someService);
CODE_SAMPLE
, [new GetAndSetToMethodCall('SomeContainer', 'addService', 'getService')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Assign::class, PropertyFetch::class];
    }
    /**
     * @param Assign|PropertyFetch $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($node instanceof Assign) {
            if ($node->var instanceof PropertyFetch) {
                return $this->processMagicSet($node->expr, $node->var);
            }
            return null;
        }
        return $this->processPropertyFetch($node);
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allIsAOf($configuration, GetAndSetToMethodCall::class);
        $this->getAndSetToMethodCalls = $configuration;
    }
    private function processMagicSet(Expr $expr, PropertyFetch $propertyFetch) : ?Node
    {
        foreach ($this->getAndSetToMethodCalls as $getAndSetToMethodCall) {
            $objectType = $getAndSetToMethodCall->getObjectType();
            if ($this->shouldSkipPropertyFetch($propertyFetch, $objectType)) {
                continue;
            }
            return $this->createMethodCallNodeFromAssignNode($propertyFetch, $expr, $getAndSetToMethodCall->getSetMethod());
        }
        return null;
    }
    private function processPropertyFetch(PropertyFetch $propertyFetch) : ?MethodCall
    {
        $parentNode = $propertyFetch->getAttribute(AttributeKey::PARENT_NODE);
        foreach ($this->getAndSetToMethodCalls as $getAndSetToMethodCall) {
            if ($this->shouldSkipPropertyFetch($propertyFetch, $getAndSetToMethodCall->getObjectType())) {
                continue;
            }
            // setter, skip
            if (!$parentNode instanceof Assign) {
                return $this->createMethodCallNodeFromPropertyFetchNode($propertyFetch, $getAndSetToMethodCall->getGetMethod());
            }
            if ($parentNode->var !== $propertyFetch) {
                return $this->createMethodCallNodeFromPropertyFetchNode($propertyFetch, $getAndSetToMethodCall->getGetMethod());
            }
        }
        return null;
    }
    private function shouldSkipPropertyFetch(PropertyFetch $propertyFetch, ObjectType $objectType) : bool
    {
        if (!$this->isObjectType($propertyFetch->var, $objectType)) {
            return \true;
        }
        if (!$this->magicPropertyFetchAnalyzer->isMagicOnType($propertyFetch, $objectType)) {
            return \true;
        }
        return $this->propertyFetchAnalyzer->isPropertyToSelf($propertyFetch);
    }
    private function createMethodCallNodeFromAssignNode(PropertyFetch $propertyFetch, Expr $expr, string $method) : MethodCall
    {
        $propertyName = $this->getName($propertyFetch->name);
        return $this->nodeFactory->createMethodCall($propertyFetch->var, $method, [$propertyName, $expr]);
    }
    private function createMethodCallNodeFromPropertyFetchNode(PropertyFetch $propertyFetch, string $method) : MethodCall
    {
        /** @var Variable $variableNode */
        $variableNode = $propertyFetch->var;
        return $this->nodeFactory->createMethodCall($variableNode, $method, [$this->getName($propertyFetch)]);
    }
}
