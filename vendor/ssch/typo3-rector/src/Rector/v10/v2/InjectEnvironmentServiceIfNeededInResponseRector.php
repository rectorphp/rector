<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v2;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Property;
use PHPStan\Type\ObjectType;
use PHPStan\Type\VerbosityLevel;
use Rector\Core\NodeManipulator\ClassInsertManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder;
use RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder;
use RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.2/Deprecation-89468-DeprecateInjectionOfEnvironmentServiceInWebRequest.html
 *
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v2\InjectEnvironmentServiceIfNeededInResponseRector\InjectEnvironmentServiceIfNeededInResponseRectorTest
 */
final class InjectEnvironmentServiceIfNeededInResponseRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const ENVIRONMENT_SERVICE = 'environmentService';
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\ClassInsertManipulator
     */
    private $classInsertManipulator;
    public function __construct(\Rector\Core\NodeManipulator\ClassInsertManipulator $classInsertManipulator)
    {
        $this->classInsertManipulator = $classInsertManipulator;
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
        if (!$this->isObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Response'))) {
            return null;
        }
        if (!$this->isPropertyEnvironmentServiceInUse($node)) {
            return null;
        }
        // already added
        $classMethod = $node->getMethod('injectEnvironmentService');
        if ($classMethod instanceof \PhpParser\Node\Stmt\ClassMethod) {
            return null;
        }
        $this->addInjectEnvironmentServiceMethod($node);
        $property = $this->createEnvironmentServiceProperty();
        $this->nodesToAddCollector->addNodeAfterNode(new \PhpParser\Node\Stmt\Nop(), $property);
        $this->classInsertManipulator->addAsFirstMethod($node, $property);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Inject EnvironmentService if needed in subclass of Response', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class MyResponse extends Response
{
    public function myMethod()
    {
        if ($this->environmentService->isEnvironmentInCliMode()) {

        }
    }
}

class MyOtherResponse extends Response
{
    public function myMethod()
    {

    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class MyResponse extends Response
{
    /**
     * @var \TYPO3\CMS\Extbase\Service\EnvironmentService
     */
    protected $environmentService;

    public function myMethod()
    {
        if ($this->environmentService->isEnvironmentInCliMode()) {

        }
    }

    public function injectEnvironmentService(\TYPO3\CMS\Extbase\Service\EnvironmentService $environmentService)
    {
        $this->environmentService = $environmentService;
    }
}

class MyOtherResponse extends Response
{
    public function myMethod()
    {

    }
}
CODE_SAMPLE
)]);
    }
    private function createEnvironmentServiceProperty() : \PhpParser\Node\Stmt\Property
    {
        $propertyBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\PropertyBuilder(self::ENVIRONMENT_SERVICE);
        $propertyBuilder->makeProtected();
        $type = new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType('TYPO3\\CMS\\Extbase\\Service\\EnvironmentService');
        $propertyBuilder->setDocComment(new \PhpParser\Comment\Doc(\sprintf('/**%s * @var \\%s%s */', \PHP_EOL, $type->describe(\PHPStan\Type\VerbosityLevel::typeOnly()), \PHP_EOL)));
        return $propertyBuilder->getNode();
    }
    private function isPropertyEnvironmentServiceInUse(\PhpParser\Node\Stmt\Class_ $class) : bool
    {
        $isEnvironmentServicePropertyUsed = \false;
        $this->traverseNodesWithCallable($class->stmts, function (\PhpParser\Node $node) use(&$isEnvironmentServicePropertyUsed) : ?PropertyFetch {
            if (!$node instanceof \PhpParser\Node\Expr\PropertyFetch) {
                return null;
            }
            if ($this->isName($node->name, 'environmentService')) {
                $isEnvironmentServicePropertyUsed = \true;
            }
            return $node;
        });
        return $isEnvironmentServicePropertyUsed;
    }
    private function addInjectEnvironmentServiceMethod(\PhpParser\Node\Stmt\Class_ $class) : void
    {
        $paramBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\ParamBuilder(self::ENVIRONMENT_SERVICE);
        $paramBuilder->setType(new \PhpParser\Node\Name\FullyQualified('TYPO3\\CMS\\Extbase\\Service\\EnvironmentService'));
        $param = $paramBuilder->getNode();
        $propertyAssignNode = $this->nodeFactory->createPropertyAssignmentWithExpr(self::ENVIRONMENT_SERVICE, new \PhpParser\Node\Expr\Variable(self::ENVIRONMENT_SERVICE));
        $classMethodBuilder = new \RectorPrefix20220501\Symplify\Astral\ValueObject\NodeBuilder\MethodBuilder('injectEnvironmentService');
        $classMethodBuilder->addParam($param);
        $classMethodBuilder->addStmt($propertyAssignNode);
        $classMethodBuilder->makePublic();
        $class->stmts[] = new \PhpParser\Node\Stmt\Nop();
        $class->stmts[] = $classMethodBuilder->getNode();
    }
}
