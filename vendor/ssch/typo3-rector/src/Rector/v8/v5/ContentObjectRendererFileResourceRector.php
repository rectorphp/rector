<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v5;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.5/Deprecation-77524-DeprecatedMethodFileResourceOfContentObjectRenderer.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v5\ContentObjectRendererFileResourceRector\ContentObjectRendererFileResourceRectorTest
 */
final class ContentObjectRendererFileResourceRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const PATH = 'path';
    /**
     * @readonly
     * @var \Ssch\TYPO3Rector\Helper\Typo3NodeResolver
     */
    private $typo3NodeResolver;
    public function __construct(\Ssch\TYPO3Rector\Helper\Typo3NodeResolver $typo3NodeResolver)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if (!$this->isName($node->name, 'fileResource')) {
            return null;
        }
        $parentNode = $node->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof \PhpParser\Node\Expr\Assign) {
            return null;
        }
        $this->addInitializeVariableNode($node);
        $this->addTypoScriptFrontendControllerAssignmentNode($node);
        $this->addFileNameNode($node);
        $this->addIfNode($node);
        $this->removeNode($parentNode);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrate fileResource method of class ContentObjectRenderer', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$template = $this->cObj->fileResource('EXT:vendor/Resources/Private/Templates/Template.html');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$path = $GLOBALS['TSFE']->tmpl->getFileName('EXT:vendor/Resources/Private/Templates/Template.html');
if ($path !== null && file_exists($path)) {
    $template = file_get_contents($path);
}
CODE_SAMPLE
)]);
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if ($this->isObjectType($methodCall->var, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'))) {
            return \false;
        }
        return !$this->typo3NodeResolver->isMethodCallOnPropertyOfGlobals($methodCall, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER, 'cObj');
    }
    private function addInitializeVariableNode(\PhpParser\Node\Expr\MethodCall $methodCall) : void
    {
        $parentNode = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$parentNode->var instanceof \PhpParser\Node\Expr\PropertyFetch) {
            $initializeVariable = new \PhpParser\Node\Stmt\Expression(new \PhpParser\Node\Expr\Assign($parentNode->var, new \PhpParser\Node\Scalar\String_('')));
            $this->nodesToAddCollector->addNodeBeforeNode($initializeVariable, $methodCall);
        }
    }
    private function addTypoScriptFrontendControllerAssignmentNode(\PhpParser\Node\Expr\MethodCall $methodCall) : void
    {
        $typoscriptFrontendControllerVariable = new \PhpParser\Node\Expr\Variable('typoscriptFrontendController');
        $typoscriptFrontendControllerAssign = new \PhpParser\Node\Expr\Assign($typoscriptFrontendControllerVariable, new \PhpParser\Node\Expr\ArrayDimFetch(new \PhpParser\Node\Expr\Variable('GLOBALS'), new \PhpParser\Node\Scalar\String_(\Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER)));
        $this->nodesToAddCollector->addNodeBeforeNode($typoscriptFrontendControllerAssign, $methodCall);
    }
    private function addFileNameNode(\PhpParser\Node\Expr\MethodCall $methodCall) : void
    {
        $fileNameAssign = new \PhpParser\Node\Expr\Assign(new \PhpParser\Node\Expr\Variable(self::PATH), $this->nodeFactory->createMethodCall($this->nodeFactory->createPropertyFetch(new \PhpParser\Node\Expr\Variable('typoscriptFrontendController'), 'tmpl'), 'getFileName', $methodCall->args));
        $this->nodesToAddCollector->addNodeBeforeNode($fileNameAssign, $methodCall);
    }
    private function addIfNode(\PhpParser\Node\Expr\MethodCall $methodCall) : void
    {
        $parentNode = $methodCall->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        $if = new \PhpParser\Node\Stmt\If_(new \PhpParser\Node\Expr\BinaryOp\BooleanAnd(new \PhpParser\Node\Expr\BinaryOp\NotIdentical(new \PhpParser\Node\Expr\Variable(self::PATH), $this->nodeFactory->createNull()), $this->nodeFactory->createFuncCall('file_exists', [new \PhpParser\Node\Expr\Variable(self::PATH)])));
        $templateAssignment = new \PhpParser\Node\Expr\Assign($parentNode->var, $this->nodeFactory->createFuncCall('file_get_contents', [new \PhpParser\Node\Expr\Variable(self::PATH)]));
        $if->stmts[] = new \PhpParser\Node\Stmt\Expression($templateAssignment);
        $this->nodesToAddCollector->addNodeBeforeNode($if, $methodCall);
    }
}
