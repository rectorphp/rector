<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v9\v4;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/9.4/Deprecation-65578-ConfigconcatenateJsAndCssAndConcatenateFiles.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v9\v4\RefactorDeprecatedConcatenateMethodsPageRendererRector\RefactorDeprecatedConcatenateMethodsPageRendererRectorTest
 */
final class RefactorDeprecatedConcatenateMethodsPageRendererRector extends \Rector\Core\Rector\AbstractRector
{
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Page\\PageRenderer'))) {
            return null;
        }
        if ($this->isName($node->name, 'getConcatenateFiles')) {
            return $this->createArrayMergeCall($node);
        }
        if ($this->isName($node->name, 'enableConcatenateFiles')) {
            return $this->splitMethodCall($node, 'enableConcatenateJavascript', 'enableConcatenateCss');
        }
        if ($this->isName($node->name, 'disableConcatenateFiles')) {
            return $this->splitMethodCall($node, 'disableConcatenateJavascript', 'disableConcatenateCss');
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns method call names to new ones.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
$files = $someObject->getConcatenateFiles();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$pageRenderer = GeneralUtility::makeInstance(PageRenderer::class);
$files = array_merge($this->getConcatenateCss(), $this->getConcatenateJavascript());
CODE_SAMPLE
)]);
    }
    private function createArrayMergeCall(\PhpParser\Node\Expr\MethodCall $methodCall) : \PhpParser\Node\Expr\FuncCall
    {
        $node1 = clone $methodCall;
        $node2 = clone $methodCall;
        $node1->name = new \PhpParser\Node\Identifier('getConcatenateCss');
        $node2->name = new \PhpParser\Node\Identifier('getConcatenateJavascript');
        return $this->nodeFactory->createFuncCall('array_merge', [new \PhpParser\Node\Arg($node1), new \PhpParser\Node\Arg($node2)]);
    }
    private function splitMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall, string $firstMethod, string $secondMethod) : \PhpParser\Node\Expr\MethodCall
    {
        $methodCall->name = new \PhpParser\Node\Identifier($firstMethod);
        $node1 = clone $methodCall;
        $node1->name = new \PhpParser\Node\Identifier($secondMethod);
        $this->nodesToAddCollector->addNodeAfterNode($node1, $methodCall);
        return $methodCall;
    }
}
