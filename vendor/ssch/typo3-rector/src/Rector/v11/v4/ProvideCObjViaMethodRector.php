<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Privatization\NodeManipulator\VisibilityManipulator;
use RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.4/Deprecation-94956-PublicCObj.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v4\ProvideCObjViaMethodRector\ProvideCObjViaMethodRectorTest
 */
final class ProvideCObjViaMethodRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const COBJ = 'cObj';
    /**
     * @readonly
     * @var \Rector\Privatization\NodeManipulator\VisibilityManipulator
     */
    private $visibilityManipulator;
    public function __construct(\Rector\Privatization\NodeManipulator\VisibilityManipulator $visibilityManipulator)
    {
        $this->visibilityManipulator = $visibilityManipulator;
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
        if ($this->shouldSkip($node)) {
            return null;
        }
        $cObjProperty = $node->getProperty(self::COBJ);
        if (!$cObjProperty instanceof \PhpParser\Node\Stmt\Property) {
            return null;
        }
        if (!$cObjProperty->isPublic()) {
            return null;
        }
        $this->visibilityManipulator->makeProtected($cObjProperty);
        $this->addSetContentObjectRendererMethod($node);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replaces public $cObj with protected and set via method', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class Foo
{
    public $cObj;
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class Foo
{
    protected $cObj;

    public function setContentObjectRenderer(ContentObjectRenderer $cObj): void
    {
        $this->cObj = $cObj;
    }
}
CODE_SAMPLE
)]);
    }
    private function addSetContentObjectRendererMethod(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        $paramBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder(self::COBJ);
        $paramBuilder->setType(new \PhpParser\Node\Name\FullyQualified('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'));
        $param = $paramBuilder->getNode();
        $propertyAssignNode = $this->nodeFactory->createPropertyAssignmentWithExpr(self::COBJ, new \PhpParser\Node\Expr\Variable(self::COBJ));
        $classMethodBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder('setContentObjectRenderer');
        $classMethodBuilder->addParam($param);
        $classMethodBuilder->addStmt($propertyAssignNode);
        $classMethodBuilder->makePublic();
        $classMethodBuilder->setReturnType('void');
        $class->stmts[] = new \PhpParser\Node\Stmt\Nop();
        $class->stmts[] = $classMethodBuilder->getNode();
    }
    private function shouldSkip(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        if ($this->isObjectType($class, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Frontend\\Plugin\\AbstractPlugin'))) {
            return \true;
        }
        if ($this->isObjectType($class, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\ActionController'))) {
            return \true;
        }
        $classMethod = $class->getMethod('setContentObjectRenderer');
        return $classMethod instanceof \PhpParser\Node\Stmt\ClassMethod;
    }
}
