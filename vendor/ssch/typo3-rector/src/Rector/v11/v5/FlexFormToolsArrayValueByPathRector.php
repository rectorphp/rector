<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v5;

use PhpParser\Node;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\Variable;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.5/Deprecation-95254-TwoFlexFormToolsMethods.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v5\FlexFormToolsArrayValueByPathRector\FlexFormToolsArrayValueByPathRectorTest
 */
final class FlexFormToolsArrayValueByPathRector extends \Rector\Core\Rector\AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Configuration\\FlexForm\\FlexFormTools'))) {
            return null;
        }
        if (!$this->isNames($node->name, ['getArrayValueByPath', 'setArrayValueByPath'])) {
            return null;
        }
        $args = [$node->args[1], $node->args[0]];
        if ($this->isName($node->name, 'getArrayValueByPath')) {
            return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\ArrayUtility', 'getValueByPath', $args);
        }
        $args[] = $node->args[2];
        $variableName = $this->getName($node->args[1]->value) ?? 'dataArray';
        $variableNode = new \PhpParser\Node\Expr\Variable($variableName);
        $staticCall = $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\ArrayUtility', 'setValueByPath', $args);
        $this->nodesToAddCollector->addNodeBeforeNode(new \PhpParser\Node\Expr\Assign($variableNode, $staticCall), $node);
        $this->removeNode($node);
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace deprecated FlexFormTools methods with ArrayUtility methods', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Configuration\FlexForm\FlexFormTools;
$flexFormTools = new FlexFormTools();
$searchArray = [];
$value = $flexFormTools->getArrayValueByPath('search/path', $searchArray);

$flexFormTools->setArrayValueByPath('set/path', $dataArray, $value);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\ArrayUtility;
$searchArray = [];
$value = ArrayUtility::getValueByPath($searchArray, 'search/path');

$dataArray = ArrayUtility::setValueByPath($dataArray, 'set/path', $value);
CODE_SAMPLE
)]);
    }
}
