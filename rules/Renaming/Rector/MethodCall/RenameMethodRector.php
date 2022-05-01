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
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\NodeManipulator\ClassManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\Renaming\Collector\MethodCallRenameCollector;
use Rector\Renaming\Contract\MethodCallRenameInterface;
use Rector\Renaming\ValueObject\MethodCallRename;
use Rector\Renaming\ValueObject\MethodCallRenameWithArrayKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20220501\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Renaming\Rector\MethodCall\RenameMethodRector\RenameMethodRectorTest
 */
final class RenameMethodRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var MethodCallRenameInterface[]
     */
    private $methodCallRenames = [];
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassManipulator
     */
    private $classManipulator;
    /**
     * @readonly
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
, [new \Rector\Renaming\ValueObject\MethodCallRename('SomeExampleClass', 'oldMethod', 'newMethod')])]);
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
            $implementsInterface = $this->classManipulator->hasParentMethodOrInterface($methodCallRename->getObjectType(), $methodCallRename->getOldMethod(), $methodCallRename->getNewMethod());
            if ($implementsInterface) {
                continue;
            }
            if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, $methodCallRename->getObjectType())) {
                continue;
            }
            if (!$this->isName($node->name, $methodCallRename->getOldMethod())) {
                continue;
            }
            if ($this->shouldSkipClassMethod($node, $methodCallRename)) {
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
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        \RectorPrefix20220501\Webmozart\Assert\Assert::allIsAOf($configuration, \Rector\Renaming\Contract\MethodCallRenameInterface::class);
        $this->methodCallRenames = $configuration;
        $this->methodCallRenameCollector->addMethodCallRenames($configuration);
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall|\PhpParser\Node\Stmt\ClassMethod $node
     */
    private function shouldSkipClassMethod($node, \Rector\Renaming\Contract\MethodCallRenameInterface $methodCallRename) : bool
    {
        if (!$node instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return \false;
        }
        return $this->shouldSkipForAlreadyExistingClassMethod($node, $methodCallRename);
    }
    private function shouldSkipForAlreadyExistingClassMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod, \Rector\Renaming\Contract\MethodCallRenameInterface $methodCallRename) : bool
    {
        $classLike = $this->betterNodeFinder->findParentType($classMethod, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \false;
        }
        return (bool) $classLike->getMethod($methodCallRename->getNewMethod());
    }
}
