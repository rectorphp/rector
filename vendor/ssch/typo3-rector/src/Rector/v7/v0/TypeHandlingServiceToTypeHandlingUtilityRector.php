<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v7\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/7.0/Breaking-61786-ExtbaseDeprecatedTypeHandlingServiceRemoved.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v7\v0\TypeHandlingServiceToTypeHandlingUtilityRector\TypeHandlingServiceToTypeHandlingUtilityRectorTest
 */
final class TypeHandlingServiceToTypeHandlingUtilityRector extends AbstractRector
{
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Extbase\\Service\\TypeHandlingService'))) {
            return null;
        }
        $methodCall = $this->getName($node->name);
        if (null === $methodCall) {
            return null;
        }
        return $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Extbase\\Utility\\TypeHandlingUtility', $methodCall, $node->args);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use TypeHandlingUtility instead of TypeHandlingService', [new CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Service\TypeHandlingService;
GeneralUtility::makeInstance(TypeHandlingService::class)->isSimpleType('string');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Extbase\Utility\TypeHandlingUtility;
TypeHandlingUtility::isSimpleType('string');
CODE_SAMPLE
)]);
    }
}
