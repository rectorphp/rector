<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v0;

use RectorPrefix20210630\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use PHPStan\Type\TypeWithClassName;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.0/Deprecation-92784-ExtbaseControllerActionsMustReturnResponseInterface.html
 *
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v0\ExtbaseControllerActionsMustReturnResponseInterfaceRector\ExtbaseControllerActionsMustReturnResponseInterfaceRectorTest
 */
final class ExtbaseControllerActionsMustReturnResponseInterfaceRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const THIS = 'this';
    /**
     * @var string
     */
    private const HTML_RESPONSE = 'htmlResponse';
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
        if ($this->shouldSkip($node)) {
            return null;
        }
        foreach ($this->extractReturnCalls($node) as $returnCall) {
            if (!$returnCall instanceof \PhpParser\Node\Stmt\Return_) {
                continue;
            }
            $returnCallExpression = $returnCall->expr;
            if ($returnCallExpression instanceof \PhpParser\Node\Expr\FuncCall && $this->isName($returnCallExpression->name, 'json_encode')) {
                $returnCall->expr = $this->nodeFactory->createMethodCall($this->nodeFactory->createPropertyFetch(self::THIS, 'responseFactory'), 'createJsonResponse', [$returnCall->expr]);
            } else {
                // avoid duplication
                if ($returnCall->expr instanceof \PhpParser\Node\Expr\MethodCall && $this->isName($returnCall->expr->name, self::HTML_RESPONSE)) {
                    $args = [];
                } else {
                    $args = [$returnCall->expr];
                }
                $returnCall->expr = $this->nodeFactory->createMethodCall(self::THIS, self::HTML_RESPONSE, $args);
            }
        }
        $node->returnType = new \PhpParser\Node\Name\FullyQualified('Psr\\Http\\Message\\ResponseInterface');
        $statements = $node->stmts;
        $lastStatement = null;
        if (\is_array($statements)) {
            $lastStatement = \array_pop($statements);
        }
        if (!$lastStatement instanceof \PhpParser\Node\Stmt\Return_) {
            $returnResponse = $this->nodeFactory->createMethodCall(self::THIS, self::HTML_RESPONSE);
            $node->stmts[] = new \PhpParser\Node\Stmt\Return_($returnResponse);
        }
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Extbase controller actions must return ResponseInterface', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
class MyController extends ActionController
{
    public function someAction()
    {
        $this->view->assign('foo', 'bar');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
class MyController extends ActionController
{
    public function someAction(): ResponseInterface
    {
        $this->view->assign('foo', 'bar');
        return $this->htmlResponse();
    }
}
CODE_SAMPLE
)]);
    }
    private function shouldSkip(\PhpParser\Node\Stmt\ClassMethod $node) : bool
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\ActionController'))) {
            return \true;
        }
        if (!$node->isPublic()) {
            return \true;
        }
        $methodName = $this->getName($node->name);
        if (null === $methodName) {
            return \true;
        }
        if (!\RectorPrefix20210630\Nette\Utils\Strings::endsWith($methodName, 'Action')) {
            return \true;
        }
        if (\RectorPrefix20210630\Nette\Utils\Strings::startsWith($methodName, 'initialize')) {
            return \true;
        }
        if ($this->hasExitCall($node)) {
            return \true;
        }
        if ($this->hasRedirectCall($node)) {
            return \true;
        }
        return $this->alreadyResponseReturnType($node);
    }
    /**
     * @return Return_[]|Node[]
     */
    private function extractReturnCalls(\PhpParser\Node\Stmt\ClassMethod $node) : array
    {
        return $this->betterNodeFinder->find((array) $node->stmts, function (\PhpParser\Node $node) : bool {
            return $node instanceof \PhpParser\Node\Stmt\Return_;
        });
    }
    private function hasRedirectCall(\PhpParser\Node\Stmt\ClassMethod $node) : bool
    {
        return (bool) $this->betterNodeFinder->find((array) $node->stmts, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
                return \false;
            }
            if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\ActionController'))) {
                return \false;
            }
            return $this->isNames($node->name, ['redirect', 'redirectToUri']);
        });
    }
    private function hasExitCall(\PhpParser\Node\Stmt\ClassMethod $node) : bool
    {
        return (bool) $this->betterNodeFinder->find((array) $node->stmts, function (\PhpParser\Node $node) : bool {
            return $node instanceof \PhpParser\Node\Expr\Exit_;
        });
    }
    private function alreadyResponseReturnType(\PhpParser\Node\Stmt\ClassMethod $node) : bool
    {
        foreach ($this->extractReturnCalls($node) as $returnCall) {
            if (!$returnCall instanceof \PhpParser\Node\Stmt\Return_) {
                continue;
            }
            if (null === $returnCall->expr) {
                continue;
            }
            $returnType = $this->nodeTypeResolver->getStaticType($returnCall->expr);
            if (!$returnType instanceof \PHPStan\Type\TypeWithClassName) {
                continue;
            }
            if ($returnType->isSuperTypeOf(new \PHPStan\Type\ObjectType('Psr\\Http\\Message\\ResponseInterface'))->yes()) {
                return \true;
            }
        }
        return \false;
    }
}
