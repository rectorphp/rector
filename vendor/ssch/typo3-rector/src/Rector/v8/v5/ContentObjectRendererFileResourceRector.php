<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v5;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\BooleanAnd;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\NotIdentical;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PhpParser\Node\Stmt\Expression;
use RectorPrefix20220606\PhpParser\Node\Stmt\If_;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.5/Deprecation-77524-DeprecatedMethodFileResourceOfContentObjectRenderer.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v5\ContentObjectRendererFileResourceRector\ContentObjectRendererFileResourceRectorTest
 */
final class ContentObjectRendererFileResourceRector extends AbstractRector
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
    public function __construct(Typo3NodeResolver $typo3NodeResolver)
    {
        $this->typo3NodeResolver = $typo3NodeResolver;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        if (!$this->isName($node->name, 'fileResource')) {
            return null;
        }
        $parentNode = $node->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode instanceof Assign) {
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
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Migrate fileResource method of class ContentObjectRenderer', [new CodeSample(<<<'CODE_SAMPLE'
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
    private function shouldSkip(MethodCall $methodCall) : bool
    {
        if ($this->isObjectType($methodCall->var, new ObjectType('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'))) {
            return \false;
        }
        return !$this->typo3NodeResolver->isMethodCallOnPropertyOfGlobals($methodCall, Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER, 'cObj');
    }
    private function addInitializeVariableNode(MethodCall $methodCall) : void
    {
        $parentNode = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        if (!$parentNode->var instanceof PropertyFetch) {
            $initializeVariable = new Expression(new Assign($parentNode->var, new String_('')));
            $this->nodesToAddCollector->addNodeBeforeNode($initializeVariable, $methodCall);
        }
    }
    private function addTypoScriptFrontendControllerAssignmentNode(MethodCall $methodCall) : void
    {
        $typoscriptFrontendControllerVariable = new Variable('typoscriptFrontendController');
        $typoscriptFrontendControllerAssign = new Assign($typoscriptFrontendControllerVariable, new ArrayDimFetch(new Variable('GLOBALS'), new String_(Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER)));
        $this->nodesToAddCollector->addNodeBeforeNode($typoscriptFrontendControllerAssign, $methodCall);
    }
    private function addFileNameNode(MethodCall $methodCall) : void
    {
        $fileNameAssign = new Assign(new Variable(self::PATH), $this->nodeFactory->createMethodCall($this->nodeFactory->createPropertyFetch(new Variable('typoscriptFrontendController'), 'tmpl'), 'getFileName', $methodCall->args));
        $this->nodesToAddCollector->addNodeBeforeNode($fileNameAssign, $methodCall);
    }
    private function addIfNode(MethodCall $methodCall) : void
    {
        $parentNode = $methodCall->getAttribute(AttributeKey::PARENT_NODE);
        $if = new If_(new BooleanAnd(new NotIdentical(new Variable(self::PATH), $this->nodeFactory->createNull()), $this->nodeFactory->createFuncCall('file_exists', [new Variable(self::PATH)])));
        $templateAssignment = new Assign($parentNode->var, $this->nodeFactory->createFuncCall('file_get_contents', [new Variable(self::PATH)]));
        $if->stmts[] = new Expression($templateAssignment);
        $this->nodesToAddCollector->addNodeBeforeNode($if, $methodCall);
    }
}
