<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v0;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Breaking-69863-RemovedDeprecatedCodeFromExtfluid.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v0\ChangeMethodCallsForStandaloneViewRector\ChangeMethodCallsForStandaloneViewRectorTest
 */
final class ChangeMethodCallsForStandaloneViewRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * class => [ oldMethod => newMethod ].
     *
     * @var array<string, array<string, string>>
     */
    private const OLD_TO_NEW_METHODS_BY_CLASS = ['TYPO3\\CMS\\Fluid\\View\\StandaloneView' => ['setLayoutRootPath' => 'setLayoutRootPaths', 'getLayoutRootPath' => 'getLayoutRootPaths', 'setPartialRootPath' => 'setPartialRootPaths', 'getPartialRootPath' => 'getPartialRootPaths']];
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Turns method call names to new ones.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
$someObject = new StandaloneView();
$someObject->setLayoutRootPath();
$someObject->getLayoutRootPath();
$someObject->setPartialRootPath();
$someObject->getPartialRootPath();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$someObject = new StandaloneView();
$someObject->setLayoutRootPaths();
$someObject->getLayoutRootPaths();
$someObject->setPartialRootPaths();
$someObject->getPartialRootPaths();
CODE_SAMPLE
)]);
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
        foreach (self::OLD_TO_NEW_METHODS_BY_CLASS as $type => $oldToNewMethods) {
            if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType($type))) {
                continue;
            }
            foreach ($oldToNewMethods as $oldMethod => $newMethod) {
                if (!$this->isName($node->name, $oldMethod)) {
                    continue;
                }
                if ($this->isNames($node->name, ['setPartialRootPath', 'setLayoutRootPath'])) {
                    $firstArgument = $node->args[0];
                    $node->name = new \PhpParser\Node\Identifier($newMethod);
                    $array = $this->nodeFactory->createArray([$firstArgument->value]);
                    $node->args = [new \PhpParser\Node\Arg($array)];
                    return $node;
                }
                if ($this->isNames($node->name, ['getLayoutRootPath', 'getPartialRootPath'])) {
                    $node->name = new \PhpParser\Node\Identifier($newMethod);
                    return $this->nodeFactory->createFuncCall('array_shift', [$node]);
                }
            }
        }
        return null;
    }
}
