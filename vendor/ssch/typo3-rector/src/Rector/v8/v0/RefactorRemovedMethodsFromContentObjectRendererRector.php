<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Breaking-72361-RemovedDeprecatedContentObjectWrappers.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v0\RefactorRemovedMethodsFromContentObjectRendererRector\RefactorRemovedMethodsFromContentObjectRendererRectorTest
 */
final class RefactorRemovedMethodsFromContentObjectRendererRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string[]
     */
    private const METHODS_TO_REFACTOR = ['FLOWPLAYER', 'TEXT', 'CLEARGIF', 'COBJ_ARRAY', 'USER', 'FILE', 'FILES', 'IMAGE', 'IMG_RESOURCE', 'IMGTEXT', 'CONTENT', 'RECORDS', 'HMENU', 'CTABLE', 'OTABLE', 'COLUMNS', 'HRULER', 'CASEFUNC', 'LOAD_REGISTER', 'FORM', 'SEARCHRESULT', 'TEMPLATE', 'FLUIDTEMPLATE', 'MULTIMEDIA', 'MEDIA', 'SWFOBJECT', 'QTOBJECT'];
    /**
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
        $methodName = $this->getName($node->name);
        if (!\in_array($methodName, self::METHODS_TO_REFACTOR, \true)) {
            return null;
        }
        $args = [$this->nodeFactory->createArg($methodName), \array_shift($node->args)];
        return $this->nodeFactory->createMethodCall($node->var, 'cObjGetSingle', $args);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Refactor removed methods from ContentObjectRenderer.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
$cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
$cObj->RECORDS(['tables' => 'tt_content', 'source' => '1,2,3']);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;
$cObj = GeneralUtility::makeInstance(ContentObjectRenderer::class);
$cObj->cObjGetSingle('RECORDS', ['tables' => 'tt_content', 'source' => '1,2,3']);
CODE_SAMPLE
)]);
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $node) : bool
    {
        if ($this->isObjectType($node->var, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'))) {
            return \false;
        }
        return !$this->typo3NodeResolver->isMethodCallOnPropertyOfGlobals($node, \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER, 'cObj');
    }
}
