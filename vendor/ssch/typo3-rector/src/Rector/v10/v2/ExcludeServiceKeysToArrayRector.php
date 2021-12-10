<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v10\v2;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.2/Deprecation-89579-ServiceChainsRequireAnArrayForExcludedServiceKeys.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v2\ExcludeServiceKeysToArrayRector\ExcludeServiceKeysToArrayRectorTest
 */
final class ExcludeServiceKeysToArrayRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var \Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer
     */
    private $arrayTypeAnalyzer;
    public function __construct(\Rector\NodeTypeResolver\TypeAnalyzer\ArrayTypeAnalyzer $arrayTypeAnalyzer)
    {
        $this->arrayTypeAnalyzer = $arrayTypeAnalyzer;
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isExpectedObjectType($node)) {
            return null;
        }
        if (!$this->isNames($node->name, ['findService', 'makeInstanceService'])) {
            return null;
        }
        $arguments = $node->args;
        if (\count($arguments) < 3) {
            return null;
        }
        $excludeServiceKeys = $arguments[2];
        if ($this->arrayTypeAnalyzer->isArrayType($excludeServiceKeys->value)) {
            return null;
        }
        $args = [new \PhpParser\Node\Scalar\String_(','), $excludeServiceKeys, $this->nodeFactory->createTrue()];
        $staticCall = $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'trimExplode', $args);
        $node->args[2] = new \PhpParser\Node\Arg($staticCall);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change parameter $excludeServiceKeys explicity to an array', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
GeneralUtility::makeInstanceService('serviceType', 'serviceSubType', 'key1, key2');
ExtensionManagementUtility::findService('serviceType', 'serviceSubType', 'key1, key2');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
GeneralUtility::makeInstanceService('serviceType', 'serviceSubType', ['key1', 'key2']);
ExtensionManagementUtility::findService('serviceType', 'serviceSubType', ['key1', 'key2']);
CODE_SAMPLE
)]);
    }
    private function isExpectedObjectType(\PhpParser\Node\Expr\StaticCall $node) : bool
    {
        if ($this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Utility\\ExtensionManagementUtility'))) {
            return \true;
        }
        return $this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'));
    }
}
