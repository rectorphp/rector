<?php

declare(strict_types=1);

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
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://doc.nette.org/en/2.4/components
 * ↓
 * @see https://symfony.com/doc/current/controller.html
 * @see \Rector\NetteToSymfony\Tests\Rector\Class_\NetteControlToSymfonyControllerRector\NetteControlToSymfonyControllerRectorTest
 */
final class NetteControlToSymfonyControllerRector extends AbstractRector
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

    public function __construct(
        ActionRenderFactory $actionRenderFactory,
        NetteClassAnalyzer $netteClassAnalyzer,
        ClassNaming $classNaming,
        ClassMethodRenderAnalyzer $classMethodRenderAnalyzer
    ) {
        $this->actionRenderFactory = $actionRenderFactory;
        $this->netteClassAnalyzer = $netteClassAnalyzer;
        $this->classNaming = $classNaming;
        $this->classMethodRenderAnalyzer = $classMethodRenderAnalyzer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Migrate Nette Component to Symfony Controller',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
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
                ),

            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->netteClassAnalyzer->isInComponent($node)) {
            return null;
        }

        $shortClassName = $this->nodeNameResolver->getShortName($node);
        $shortClassName = $this->classNaming->replaceSuffix($shortClassName, 'Control', 'Controller');

        $node->name = new Identifier($shortClassName);

        $node->extends = new FullyQualified(AbstractController::class);

        $classMethod = $node->getMethod('render');
        if ($classMethod !== null) {
            $this->processRenderMethod($classMethod);
        }

        return $node;
    }

    private function processRenderMethod(ClassMethod $classMethod): void
    {
        $this->processGetPresenterGetSessionMethodCall($classMethod);

        $classMethod->name = new Identifier('action');

        $classMethodRender = $this->classMethodRenderAnalyzer->collectFromClassMethod($classMethod);
        $methodCall = $this->actionRenderFactory->createThisRenderMethodCall($classMethodRender);

        // add return in the end
        $return = new Return_($methodCall);
        $classMethod->stmts[] = $return;

        if ($this->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            $classMethod->returnType = new FullyQualified(Response::class);
        }

        $this->removeNodes($classMethodRender->getNodesToRemove());
    }

    private function processGetPresenterGetSessionMethodCall(ClassMethod $classMethod): void
    {
        $this->traverseNodesWithCallable((array) $classMethod->getStmts(), function (Node $node): ?MethodCall {
            if (! $node instanceof MethodCall) {
                return null;
            }

            if (! $this->isName($node->name, 'getSession')) {
                return null;
            }

            if (! $node->var instanceof MethodCall) {
                return null;
            }

            if (! $this->isName($node->var->name, 'getPresenter')) {
                return null;
            }

            $node->var = new PropertyFetch(new Variable('this'), 'session');

            $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
            if (! $classLike instanceof Class_) {
                throw new ShouldNotHappenException();
            }

            $this->addConstructorDependencyToClass(
                $classLike,
                new FullyQualifiedObjectType('Nette\Http\Session'),
                'session'
            );

            return $node;
        });
    }
}
