<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v0;

use PhpParser\Builder\Method;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\Defluent\NodeAnalyzer\SameClassMethodCallAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.0/Breaking-93041-RemoveTypoScriptOptionAddQueryStringmethod.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v0\RemoveAddQueryStringMethodRector\RemoveAddQueryStringMethodRectorTest
 */
final class RemoveAddQueryStringMethodRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\Defluent\NodeAnalyzer\SameClassMethodCallAnalyzer
     */
    private $sameClassMethodCallAnalyzer;
    public function __construct(\Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer, \Rector\Defluent\NodeAnalyzer\SameClassMethodCallAnalyzer $sameClassMethodCallAnalyzer)
    {
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
        $this->sameClassMethodCallAnalyzer = $sameClassMethodCallAnalyzer;
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
        if ($this->isMethodCallOnContentObjectRenderer($node)) {
            $this->refactorGetQueryArgumentsMethodCall($node);
            return null;
        }
        return $this->refactorSetAddQueryStringMethodCall($node);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove TypoScript option addQueryString.method', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$this->uriBuilder->setUseCacheHash(true)
                         ->setCreateAbsoluteUri(true)
                         ->setAddQueryString(true)
                         ->setAddQueryStringMethod('GET')
                         ->build();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$this->uriBuilder->setUseCacheHash(true)
                         ->setCreateAbsoluteUri(true)
                         ->setAddQueryString(true)
                         ->build();
CODE_SAMPLE
)]);
    }
    private function shouldSkip(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if ($this->isMethodCallOnUriBuilder($methodCall)) {
            return \false;
        }
        return !$this->isMethodCallOnContentObjectRenderer($methodCall);
    }
    private function isMethodCallOnUriBuilder(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($methodCall, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Extbase\\Mvc\\Web\\Routing\\UriBuilder'))) {
            return \false;
        }
        return $this->isName($methodCall->name, 'setAddQueryStringMethod');
    }
    private function isMethodCallOnContentObjectRenderer(\PhpParser\Node\Expr\MethodCall $methodCall) : bool
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($methodCall, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Frontend\\ContentObject\\ContentObjectRenderer'))) {
            return \false;
        }
        return $this->isName($methodCall->name, 'getQueryArguments');
    }
    private function refactorSetAddQueryStringMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node
    {
        try {
            // If it is the only method call, we can safely delete the node here.
            $this->removeNode($methodCall);
            return $methodCall;
        } catch (\Rector\Core\Exception\ShouldNotHappenException $exception) {
            $chainMethodCalls = $this->fluentChainMethodCallNodeAnalyzer->collectAllMethodCallsInChain($methodCall);
            if (!$this->sameClassMethodCallAnalyzer->haveSingleClass($chainMethodCalls)) {
                return null;
            }
            foreach ($chainMethodCalls as $chainMethodCall) {
                if ($this->isName($methodCall->name, 'setAddQueryStringMethod')) {
                    continue;
                }
                $methodCall->var = new \PhpParser\Node\Expr\MethodCall($chainMethodCall->var, $chainMethodCall->name, $chainMethodCall->args);
            }
            return $methodCall->var;
        }
    }
    private function refactorGetQueryArgumentsMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : void
    {
        unset($methodCall->args[1], $methodCall->args[2]);
    }
}
