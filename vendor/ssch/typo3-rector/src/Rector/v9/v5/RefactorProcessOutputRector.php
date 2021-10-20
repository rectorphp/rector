<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v5;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.5/Deprecation-86486-TypoScriptFrontendController-processOutput.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v5\RefactorProcessOutputRector\RefactorProcessOutputRectorTest
 */
final class RefactorProcessOutputRector extends \Rector\Core\Rector\AbstractRector
{
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
        if ($this->typo3NodeResolver->isMethodCallOnGlobals($node, 'processOutput', \Ssch\TYPO3Rector\Helper\Typo3NodeResolver::TYPO_SCRIPT_FRONTEND_CONTROLLER)) {
            $this->refactorToNewMethodCalls($node);
            return null;
        }
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Frontend\\Controller\\TypoScriptFrontendController'))) {
            return null;
        }
        if (!$this->isName($node->name, 'processOutput')) {
            return null;
        }
        $this->refactorToNewMethodCalls($node);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('TypoScriptFrontendController->processOutput() to TypoScriptFrontendController->applyHttpHeadersToResponse() and TypoScriptFrontendController->processContentForOutput()', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;

$tsfe = GeneralUtility::makeInstance(TypoScriptFrontendController::class);
$tsfe->processOutput();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Frontend\Controller\TypoScriptFrontendController;
use TYPO3\CMS\Core\Http\Response;

$tsfe = GeneralUtility::makeInstance(TypoScriptFrontendController::class);
$tsfe->applyHttpHeadersToResponse(new Response());
$tsfe->processContentForOutput();
CODE_SAMPLE
)]);
    }
    private function refactorToNewMethodCalls(\PhpParser\Node\Expr\MethodCall $node) : void
    {
        $node->name = new \PhpParser\Node\Identifier('applyHttpHeadersToResponse');
        $response = new \PhpParser\Node\Expr\New_(new \PhpParser\Node\Name\FullyQualified('TYPO3\\CMS\\Core\\Http\\Response'));
        $node->args[0] = $this->nodeFactory->createArg($response);
        $newNode = $this->nodeFactory->createMethodCall($node->var, 'processContentForOutput');
        $this->addNodeAfterNode($newNode, $node);
    }
}
