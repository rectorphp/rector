<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.0/Deprecation-92815-ActionControllerForward.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v0\ForwardResponseInsteadOfForwardMethodRector\ForwardResponseInsteadOfForwardMethodRectorTest
 */
final class ForwardResponseInsteadOfForwardMethodRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Return TYPO3\\CMS\\Extbase\\Http\\ForwardResponse instead of TYPO3\\CMS\\Extbase\\Mvc\\Controller\\ActionController::forward()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
class FooController extends ActionController
{
   public function listAction()
   {
        $this->forward('show');
   }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Extbase\Http\ForwardResponse;

class FooController extends ActionController
{
   public function listAction(): ResponseInterface
   {
        return new ForwardResponse('show');
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
        return [\PhpParser\Node\Stmt\ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        $forwardMethodCalls = $this->extractForwardMethodCalls($node);
        if ([] === $forwardMethodCalls) {
            return null;
        }
        foreach ($forwardMethodCalls as $forwardMethodCall) {
            $action = $this->valueResolver->getValue($forwardMethodCall->args[0]->value);
            if (null === $action) {
                return null;
            }
            $args = $this->nodeFactory->createArgs([$action]);
            $forwardResponse = new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified('TYPO3\\CMS\\Extbase\\Http\\ForwardResponse'), $args);
            if (isset($forwardMethodCall->args[1]) && !$this->valueResolver->isNull($forwardMethodCall->args[1]->value)) {
                $forwardResponse = $this->nodeFactory->createMethodCall($forwardResponse, 'withControllerName', [$forwardMethodCall->args[1]->value]);
            }
            if (isset($forwardMethodCall->args[2]) && !$this->valueResolver->isNull($forwardMethodCall->args[2]->value)) {
                $forwardResponse = $this->nodeFactory->createMethodCall($forwardResponse, 'withExtensionName', [$forwardMethodCall->args[2]->value]);
            }
            if (isset($forwardMethodCall->args[3])) {
                $forwardResponse = $this->nodeFactory->createMethodCall($forwardResponse, 'withArguments', [$forwardMethodCall->args[3]->value]);
            }
            $returnForwardResponse = new \PhpParser\Node\Stmt\Return_($forwardResponse);
            $this->nodesToAddCollector->addNodeBeforeNode($returnForwardResponse, $forwardMethodCall);
            $this->removeNode($forwardMethodCall);
        }
        // Add returnType only if it is the only statement, otherwise it is not reliable
        if ((\is_array($node->stmts) || $node->stmts instanceof \Countable) && 1 === \count($node->stmts)) {
            $node->returnType = new \PhpParser\Node\Name\FullyQualified('Psr\\Http\\Message\\ResponseInterface');
        }
        return $node;
    }
    /**
     * @return MethodCall[]
     */
    private function extractForwardMethodCalls(\PhpParser\Node\Stmt\ClassMethod $node) : array
    {
        return $this->betterNodeFinder->find((array) $node->stmts, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
                return \false;
            }
            if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\ActionController'))) {
                return \false;
            }
            return $this->isName($node->name, 'forward');
        });
    }
}
