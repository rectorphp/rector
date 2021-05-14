<?php

declare (strict_types=1);
namespace Rector\Renaming\Rector\MethodCall;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NodeManipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Renaming\Collector\MethodCallRenameCollector;
use Rector\Renaming\Contract\MethodCallRenameInterface;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20210514\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\RenameMethodRectorTest
 */
final class RenameMethodRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const METHOD_CALL_RENAMES = 'method_call_renames';
    /**
     * @var MethodCallRenameInterface[]
     */
    private $methodCallRenames = [];
    /**
     * @var \Rector\Core\NodeManipulator\ClassManipulator
     */
    private $classManipulator;
    /**
     * @var \Rector\Renaming\Collector\MethodCallRenameCollector
     */
    private $methodCallRenameCollector;
    public function __construct(\Rector\Core\NodeManipulator\ClassManipulator $classManipulator, \Rector\Renaming\Collector\MethodCallRenameCollector $methodCallRenameCollector)
    {
        $this->classManipulator = $classManipulator;
        $this->methodCallRenameCollector = $methodCallRenameCollector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns method names to new ones.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->oldMethod();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$someObject = new SomeExampleClass;
$someObject->newMethod();
CODE_SAMPLE
, [self::METHOD_CALL_RENAMES => [new \Rector\Renaming\ValueObject\MethodCallRename('SomeExampleClass', 'oldMethod', 'newMethod')]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class, \PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($this->methodCallRenames as $methodCallRename) {
            $implementsInterface = $this->classManipulator->hasParentMethodOrInterface($methodCallRename->getOldObjectType(), $methodCallRename->getOldMethod());
            if ($implementsInterface) {
                continue;
            }
            if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, $methodCallRename->getOldObjectType())) {
                continue;
            }
            if (!$this->isName($node->name, $methodCallRename->getOldMethod())) {
                continue;
            }
            if ($this->skipClassMethod($node, $methodCallRename)) {
                continue;
            }
            $node->name = new \PhpParser\Node\Identifier($methodCallRename->getNewMethod());
            if ($methodCallRename instanceof \Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey && !$node instanceof \PhpParser\Node\Stmt\ClassMethod) {
                return new \PhpParser\Node\Expr\ArrayDimFetch($node, \PhpParser\BuilderHelpers::normalizeValue($methodCallRename->getArrayKey()));
            }
            return $node;
        }
        return null;
    }
    /**
     * @param array<string, MethodCallRenameInterface[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $methodCallRenames = $configuration[self::METHOD_CALL_RENAMES] ?? [];
        \RectorPrefix20210514\Webmozart\Assert\Assert::allIsInstanceOf($methodCallRenames, \Rector\Renaming\Contract\MethodCallRenameInterface::class);
        $this->methodCallRenames = $methodCallRenames;
        $this->methodCallRenameCollector->addMethodCallRenames($methodCallRenames);
    }
    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    private function skipClassMethod(\PhpParser\Node $node, \Rector\Renaming\Contract\MethodCallRenameInterface $methodCallRename) : bool
    {
        if (!$node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        if ($this->shouldSkipForAlreadyExistingClassMethod($node, $methodCallRename)) {
            return \true;
        }
        return $this->shouldSkipForExactClassMethodForClassMethodOrTargetInvokePrivate($node, $methodCallRename->getOldObjectType(), $methodCallRename->getNewMethod());
    }
    private function shouldSkipForAlreadyExistingClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, \Rector\Renaming\Contract\MethodCallRenameInterface $methodCallRename) : bool
    {
        $classLike = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \false;
        }
        return (bool) $classLike->getMethod($methodCallRename->getNewMethod());
    }
    private function shouldSkipForExactClassMethodForClassMethodOrTargetInvokePrivate(\PhpParser\Node\Stmt\ClassMethod $classMethod, \PHPStan\Type\ObjectType $objectType, string $newMethodName) : bool
    {
        $className = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        $methodCalls = $this->nodeRepository->findMethodCallsOnClass($className);
        $name = $this->getName($classMethod->name);
        if (isset($methodCalls[$name])) {
            return \false;
        }
        $classMethodClass = $classMethod->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NAME);
        if ($classMethodClass === $objectType) {
            return \true;
        }
        if ($classMethod->isPublic()) {
            return \false;
        }
        $newClassMethod = clone $classMethod;
        $newClassMethod->name = new \PhpParser\Node\Identifier($newMethodName);
        return $newClassMethod->isMagic();
    }
}
