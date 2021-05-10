<?php

declare (strict_types=1);
namespace Rector\NetteToSymfony\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use Rector\CodingStyle\Naming\ClassNaming;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\Nette\NodeAnalyzer\NetteClassAnalyzer;
use Rector\Nette\NodeFactory\ActionRenderFactory;
use Rector\NetteToSymfony\NodeAnalyzer\ClassMethodRenderAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://doc.nette.org/en/2.4/components https://symfony.com/doc/current/controller.html
 *
 * @see \Rector\NetteToSymfony\Tests\Rector\Class_\NetteControlToSymfonyControllerRector\NetteControlToSymfonyControllerRectorTest
 */
final class NetteControlToSymfonyControllerRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var ActionRenderFactory
     */
    private $actionRenderFactory;
    /**
     * @var NetteClassAnalyzer
     */
    private $netteClassAnalyzer;
    /**
     * @var ClassNaming
     */
    private $classNaming;
    /**
     * @var ClassMethodRenderAnalyzer
     */
    private $classMethodRenderAnalyzer;
    public function __construct(\Rector\Nette\NodeFactory\ActionRenderFactory $actionRenderFactory, \Rector\Nette\NodeAnalyzer\NetteClassAnalyzer $netteClassAnalyzer, \Rector\CodingStyle\Naming\ClassNaming $classNaming, \Rector\NetteToSymfony\NodeAnalyzer\ClassMethodRenderAnalyzer $classMethodRenderAnalyzer)
    {
        $this->actionRenderFactory = $actionRenderFactory;
        $this->netteClassAnalyzer = $netteClassAnalyzer;
        $this->classNaming = $classNaming;
        $this->classMethodRenderAnalyzer = $classMethodRenderAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrate Nette Component to Symfony Controller', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Nette\Application\UI\Control;

class SomeControl extends Control
{
    public function render()
    {
        $this->template->param = 'some value';
        $this->template->render(__DIR__ . '/poll.latte');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;

class SomeController extends AbstractController
{
     public function some(): Response
     {
         return $this->render(__DIR__ . '/poll.latte', ['param' => 'some value']);
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
        if (!$this->netteClassAnalyzer->isInComponent($node)) {
            return null;
        }
        $shortClassName = $this->nodeNameResolver->getShortName($node);
        $shortClassName = $this->classNaming->replaceSuffix($shortClassName, 'Control', 'Controller');
        $node->name = new \PhpParser\Node\Identifier($shortClassName);
        $node->extends = new \PhpParser\Node\Name\FullyQualified('Symfony\\Bundle\\FrameworkBundle\\Controller\\AbstractController');
        $classMethod = $node->getMethod('render');
        if ($classMethod !== null) {
            $this->processRenderMethod($classMethod);
        }
        return $node;
    }
    private function processRenderMethod(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        $this->processGetPresenterGetSessionMethodCall($classMethod);
        $classMethod->name = new \PhpParser\Node\Identifier('action');
        $classMethodRender = $this->classMethodRenderAnalyzer->collectFromClassMethod($classMethod);
        $methodCall = $this->actionRenderFactory->createThisRenderMethodCall($classMethodRender);
        // add return in the end
        $return = new \PhpParser\Node\Stmt\Return_($methodCall);
        $classMethod->stmts[] = $return;
        if ($this->isAtLeastPhpVersion(\Rector\Core\ValueObject\PhpVersionFeature::SCALAR_TYPES)) {
            $classMethod->returnType = new \PhpParser\Node\Name\FullyQualified('Symfony\\Component\\HttpFoundation\\Response');
        }
        $this->removeNodes($classMethodRender->getNodesToRemove());
    }
    private function processGetPresenterGetSessionMethodCall(\PhpParser\Node\Stmt\ClassMethod $classMethod) : void
    {
        $this->traverseNodesWithCallable((array) $classMethod->getStmts(), function (\PhpParser\Node $node) : ?MethodCall {
            if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
                return null;
            }
            if (!$this->isName($node->name, 'getSession')) {
                return null;
            }
            if (!$node->var instanceof \PhpParser\Node\Expr\MethodCall) {
                return null;
            }
            if (!$this->isName($node->var->name, 'getPresenter')) {
                return null;
            }
            $node->var = new \PhpParser\Node\Expr\PropertyFetch(new \PhpParser\Node\Expr\Variable('this'), 'session');
            $classLike = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::CLASS_NODE);
            if (!$classLike instanceof \PhpParser\Node\Stmt\Class_) {
                throw new \Rector\Core\Exception\ShouldNotHappenException();
            }
            $this->addConstructorDependencyToClass($classLike, new \Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType('Nette\\Http\\Session'), 'session');
            return $node;
        });
    }
}
