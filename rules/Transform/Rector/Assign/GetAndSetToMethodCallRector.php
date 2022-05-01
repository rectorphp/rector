<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\Assign;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer;
use Rector\Core\NodeManipulator\MagicPropertyFetchAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Transform\ValueObject\GetAndSetToMethodCall;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\Assign\GetAndSetToMethodCallRector\GetAndSetToMethodCallRectorTest
 */
final class GetAndSetToMethodCallRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
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
    public function __construct(\Rector\Core\NodeAnalyzer\PropertyFetchAnalyzer $propertyFetchAnalyzer, \Rector\Core\NodeManipulator\MagicPropertyFetchAnalyzer $magicPropertyFetchAnalyzer)
    {
        $this->propertyFetchAnalyzer = $propertyFetchAnalyzer;
        $this->magicPropertyFetchAnalyzer = $magicPropertyFetchAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns defined `__get`/`__set` to specific method calls.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
$container = new SomeContainer;
$container->someService = $someService;
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$container = new SomeContainer;
$container->setService("someService", $someService);
CODE_SAMPLE
, [new \Rector\Transform\ValueObject\GetAndSetToMethodCall('SomeContainer', 'addService', 'getService')])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\Assign::class, \PhpParser\Node\Expr\PropertyFetch::class];
    }
    /**
     * @param Assign|PropertyFetch $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($node instanceof \PhpParser\Node\Expr\Assign) {
            if ($node->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
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
        \RectorPrefix20220501\Webmozart\Assert\Assert::allIsAOf($configuration, \Rector\Transform\ValueObject\GetAndSetToMethodCall::class);
        $this->getAndSetToMethodCalls = $configuration;
    }
    private function processMagicSet(\PhpParser\Node\Expr $expr, \PhpParser\Node\Expr\PropertyFetch $propertyFetch) : ?\PhpParser\Node
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
    private function processPropertyFetch(\PhpParser\Node\Expr\PropertyFetch $propertyFetch) : ?\PhpParser\Node\Expr\MethodCall
    {
        $parentNode = $propertyFetch->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        foreach ($this->getAndSetToMethodCalls as $getAndSetToMethodCall) {
            if ($this->shouldSkipPropertyFetch($propertyFetch, $getAndSetToMethodCall->getObjectType())) {
                continue;
            }
            // setter, skip
            if (!$parentNode instanceof \PhpParser\Node\Expr\Assign) {
                return $this->createMethodCallNodeFromPropertyFetchNode($propertyFetch, $getAndSetToMethodCall->getGetMethod());
            }
            if ($parentNode->var !== $propertyFetch) {
                return $this->createMethodCallNodeFromPropertyFetchNode($propertyFetch, $getAndSetToMethodCall->getGetMethod());
            }
        }
        return null;
    }
    private function shouldSkipPropertyFetch(\PhpParser\Node\Expr\PropertyFetch $propertyFetch, \PHPStan\Type\ObjectType $objectType) : bool
    {
        if (!$this->isObjectType($propertyFetch->var, $objectType)) {
            return \true;
        }
        if (!$this->magicPropertyFetchAnalyzer->isMagicOnType($propertyFetch, $objectType)) {
            return \true;
        }
        return $this->propertyFetchAnalyzer->isPropertyToSelf($propertyFetch);
    }
    private function createMethodCallNodeFromAssignNode(\PhpParser\Node\Expr\PropertyFetch $propertyFetch, \PhpParser\Node\Expr $expr, string $method) : \PhpParser\Node\Expr\MethodCall
    {
        $propertyName = $this->getName($propertyFetch->name);
        return $this->nodeFactory->createMethodCall($propertyFetch->var, $method, [$propertyName, $expr]);
    }
    private function createMethodCallNodeFromPropertyFetchNode(\PhpParser\Node\Expr\PropertyFetch $propertyFetch, string $method) : \PhpParser\Node\Expr\MethodCall
    {
        /** @var Variable $variableNode */
        $variableNode = $propertyFetch->var;
        return $this->nodeFactory->createMethodCall($variableNode, $method, [$this->getName($propertyFetch)]);
    }
}
