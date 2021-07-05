<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v8\v0;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Deprecation-73050-DeprecatedRandomGeneratorMethodsInGeneralUtility.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v0\RandomMethodsToRandomClassRector\RandomMethodsToRandomClassRectorTest
 */
final class RandomMethodsToRandomClassRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const GENERATE_RANDOM_BYTES = 'generateRandomBytes';
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'))) {
            return null;
        }
        if (!$this->isNames($node->name, [self::GENERATE_RANDOM_BYTES, 'getRandomHexString'])) {
            return null;
        }
        $randomClass = $this->nodeFactory->createStaticCall('TYPO3\\CMS\\Core\\Utility\\GeneralUtility', 'makeInstance', [$this->nodeFactory->createClassConstReference('TYPO3\\CMS\\Core\\Crypto\\Random')]);
        if ($this->isName($node->name, self::GENERATE_RANDOM_BYTES)) {
            return $this->nodeFactory->createMethodCall($randomClass, self::GENERATE_RANDOM_BYTES, $node->args);
        }
        return $this->nodeFactory->createMethodCall($randomClass, 'generateRandomHexString', $node->args);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Deprecated random generator methods in GeneralUtility', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;

$randomBytes = GeneralUtility::generateRandomBytes();
$randomHex = GeneralUtility::getRandomHexString();
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Crypto\Random;
use TYPO3\CMS\Core\Utility\GeneralUtility;
$randomBytes = GeneralUtility::makeInstance(Random::class)->generateRandomBytes();
$randomHex = GeneralUtility::makeInstance(Random::class)->generateRandomHexString();
CODE_SAMPLE
)]);
    }
}
