<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v0;

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
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.0/Deprecation-89938-DeprecatedLanguageModeInTypo3QuerySettings.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v0\RemoveLanguageModeMethodsFromTypo3QuerySettingsRector\RemoveLanguageModeMethodsFromTypo3QuerySettingsRectorTest
 */
final class RemoveLanguageModeMethodsFromTypo3QuerySettingsRector extends \Rector\Core\Rector\AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Extbase\\Persistence\\Generic\\Typo3QuerySettings'))) {
            return null;
        }
        if (!$this->isNames($node->name, ['setLanguageMode', 'getLanguageMode'])) {
            return null;
        }
        return $this->removeMethodCall($node);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove language mode methods from class Typo3QuerySettings', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
$querySettings = new Typo3QuerySettings();
$querySettings->setLanguageUid(0)->setLanguageMode()->getLanguageMode();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
$querySettings = new Typo3QuerySettings();
$querySettings->setLanguageUid(0);
CODE_SAMPLE
)]);
    }
    private function removeMethodCall(\PhpParser\Node\Expr\MethodCall $methodCall) : ?\PhpParser\Node
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
                if ($this->isNames($chainMethodCall->name, ['setLanguageMode', 'getLanguageMode'])) {
                    continue;
                }
                $methodCall->var = new \PhpParser\Node\Expr\MethodCall($chainMethodCall->var, $chainMethodCall->name, $chainMethodCall->args);
            }
            return $methodCall->var;
        }
    }
}
