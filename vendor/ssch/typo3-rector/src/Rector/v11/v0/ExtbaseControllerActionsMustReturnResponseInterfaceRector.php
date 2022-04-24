<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\Exit_;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use PHPStan\Type\ObjectType;
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
        $returns = $this->findReturns($node);
        foreach ($returns as $return) {
            $returnCallExpression = $return->expr;
            if ($returnCallExpression instanceof \PhpParser\Node\Expr\FuncCall && $this->isName($returnCallExpression->name, 'json_encode')) {
                $return->expr = $this->nodeFactory->createMethodCall(self::THIS, 'jsonResponse', [$return->expr]);
            } else {
                // avoid duplication
                if ($return->expr instanceof \PhpParser\Node\Expr\MethodCall && $this->isName($return->expr->name, self::HTML_RESPONSE)) {
                    $args = [];
                } else {
                    $args = [$return->expr];
                }
                $return->expr = $this->nodeFactory->createMethodCall(self::THIS, self::HTML_RESPONSE, $args);
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
    private function shouldSkip(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($classMethod, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\ActionController'))) {
            return \true;
        }
        if (!$classMethod->isPublic()) {
            return \true;
        }
        if ($classMethod->isAbstract()) {
            return \true;
        }
        $methodName = $this->getName($classMethod->name);
        if (null === $methodName) {
            return \true;
        }
        if (\substr_compare($methodName, 'Action', -\strlen('Action')) !== 0) {
            return \true;
        }
        if (\strncmp($methodName, 'initialize', \strlen('initialize')) === 0) {
            return \true;
        }
        if ($this->lastStatementIsExitCall($classMethod)) {
            return \true;
        }
        if ($this->hasRedirectCall($classMethod)) {
            return \true;
        }
        if ($this->lastStatementIsForwardCall($classMethod)) {
            return \true;
        }
        if ($this->hasExceptionCall($classMethod)) {
            return \true;
        }
        return $this->isAlreadyResponseReturnType($classMethod);
    }
    /**
     * @return Return_[]
     */
    private function findReturns(\PhpParser\Node\Stmt\ClassMethod $classMethod) : array
    {
        return $this->betterNodeFinder->findInstanceOf((array) $classMethod->stmts, \PhpParser\Node\Stmt\Return_::class);
    }
    private function hasRedirectCall(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        return (bool) $this->betterNodeFinder->find((array) $classMethod->stmts, function (\PhpParser\Node $node) : bool {
            if (!$node instanceof \PhpParser\Node\Expr\MethodCall) {
                return \false;
            }
            if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Extbase\\Mvc\\Controller\\ActionController'))) {
                return \false;
            }
            return $this->isNames($node->name, ['redirect', 'redirectToUri']);
        });
    }
    private function lastStatementIsExitCall(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if (null === $classMethod->stmts) {
            return \false;
        }
        $statements = $classMethod->stmts;
        $lastStatement = \array_pop($statements);
        return $lastStatement instanceof \PhpParser\Node\Stmt\Expression && $lastStatement->expr instanceof \PhpParser\Node\Expr\Exit_;
    }
    private function isAlreadyResponseReturnType(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        $returns = $this->findReturns($classMethod);
        $responseObjectType = new \PHPStan\Type\ObjectType('Psr\\Http\\Message\\ResponseInterface');
        foreach ($returns as $return) {
            if (null === $return->expr) {
                continue;
            }
            $returnType = $this->getType($return->expr);
            if ($returnType->isSuperTypeOf($responseObjectType)->yes()) {
                return \true;
            }
            if ($returnType instanceof \PHPStan\Type\ObjectType && $returnType->isInstanceOf('Psr\\Http\\Message\\ResponseInterface')->yes()) {
                return \true;
            }
        }
        return \false;
    }
    private function hasExceptionCall(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if (null === $classMethod->stmts) {
            return \false;
        }
        $statements = $classMethod->stmts;
        $lastStatement = \array_pop($statements);
        if (!$lastStatement instanceof \PhpParser\Node\Stmt\Throw_) {
            return \false;
        }
        $propagateResponseException = new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Http\\PropagateResponseException');
        return $this->getType($lastStatement->expr)->isSuperTypeOf($propagateResponseException)->yes();
    }
    private function lastStatementIsForwardCall(\PhpParser\Node\Stmt\ClassMethod $classMethod) : bool
    {
        if (null === $classMethod->stmts) {
            return \false;
        }
        $statements = $classMethod->stmts;
        $lastStatement = \array_pop($statements);
        if (!$lastStatement instanceof \PhpParser\Node\Stmt\Expression) {
            return \false;
        }
        if (!$lastStatement->expr instanceof \PhpParser\Node\Expr\MethodCall) {
            return \false;
        }
        return $this->isName($lastStatement->expr->name, 'forward');
    }
}
