<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\NodeAnalyzer\ClassAnalyzer;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\MethodName;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use Rector\Transform\NodeAnalyzer\SingletonClassMethodAnalyzer;
use Rector\Transform\ValueObject\PropertyAndClassMethodName;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://3v4l.org/lifbH
 * @changelog https://stackoverflow.com/a/203359/1348344
 * @changelog http://cleancode.blog/2017/07/20/how-to-avoid-many-instances-in-singleton-pattern/
 * @see \Rector\Tests\Transform\Rector\Class_\ChangeSingletonToServiceRector\ChangeSingletonToServiceRectorTest
 */
final class ChangeSingletonToServiceRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Transform\NodeAnalyzer\SingletonClassMethodAnalyzer
     */
    private $singletonClassMethodAnalyzer;
    /**
     * @readonly
     * @var \Rector\Core\NodeAnalyzer\ClassAnalyzer
     */
    private $classAnalyzer;
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    public function __construct(\Rector\Transform\NodeAnalyzer\SingletonClassMethodAnalyzer $singletonClassMethodAnalyzer, \Rector\Core\NodeAnalyzer\ClassAnalyzer $classAnalyzer, \Rector\Privatization\NodeManipulator\VisibilityManipulator $visibilityManipulator)
    {
        $this->singletonClassMethodAnalyzer = $singletonClassMethodAnalyzer;
        $this->classAnalyzer = $classAnalyzer;
        $this->visibilityManipulator = $visibilityManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change singleton class to normal class that can be registered as a service', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    private static $instance;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (null === static::$instance) {
            static::$instance = new static();
        }

        return static::$instance;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function __construct()
    {
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Stmt\Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->classAnalyzer->isAnonymousClass($node)) {
            return null;
        }
        $propertyAndClassMethodName = $this->matchStaticPropertyFetchAndGetSingletonMethodName($node);
        if (!$propertyAndClassMethodName instanceof \Rector\Transform\ValueObject\PropertyAndClassMethodName) {
            return null;
        }
        return $this->refactorClassStmts($node, $propertyAndClassMethodName);
    }
    private function matchStaticPropertyFetchAndGetSingletonMethodName(\PhpParser\Node\Stmt\Class_ $class) : ?\Rector\Transform\ValueObject\PropertyAndClassMethodName
    {
        foreach ($class->getMethods() as $classMethod) {
            if (!$classMethod->isStatic()) {
                continue;
            }
            $staticPropertyFetch = $this->singletonClassMethodAnalyzer->matchStaticPropertyFetch($classMethod);
            if (!$staticPropertyFetch instanceof \PhpParser\Node\Expr\StaticPropertyFetch) {
                return null;
            }
            /** @var string $propertyName */
            $propertyName = $this->getName($staticPropertyFetch);
            /** @var string $classMethodName */
            $classMethodName = $this->getName($classMethod);
            return new \Rector\Transform\ValueObject\PropertyAndClassMethodName($propertyName, $classMethodName);
        }
        return null;
    }
    private function refactorClassStmts(\PhpParser\Node\Stmt\Class_ $class, \Rector\Transform\ValueObject\PropertyAndClassMethodName $propertyAndClassMethodName) : \PhpParser\Node\Stmt\Class_
    {
        foreach ($class->getMethods() as $classMethod) {
            if ($this->isName($classMethod, $propertyAndClassMethodName->getClassMethodName())) {
                $this->nodeRemover->removeNodeFromStatements($class, $classMethod);
                continue;
            }
            if (!$this->isNames($classMethod, [\Rector\Core\ValueObject\MethodName::CONSTRUCT, \Rector\Core\ValueObject\MethodName::CLONE, '__wakeup'])) {
                continue;
            }
            if ($classMethod->isPublic()) {
                continue;
            }
            // remove non-public empty
            if ($classMethod->stmts === []) {
                $this->nodeRemover->removeNodeFromStatements($class, $classMethod);
            } else {
                $this->visibilityManipulator->makePublic($classMethod);
            }
        }
        $this->removePropertyByName($class, $propertyAndClassMethodName->getPropertyName());
        return $class;
    }
    private function removePropertyByName(\PhpParser\Node\Stmt\Class_ $class, string $propertyName) : void
    {
        foreach ($class->getProperties() as $property) {
            if (!$this->isName($property, $propertyName)) {
                continue;
            }
            $this->nodeRemover->removeNodeFromStatements($class, $property);
        }
    }
}
