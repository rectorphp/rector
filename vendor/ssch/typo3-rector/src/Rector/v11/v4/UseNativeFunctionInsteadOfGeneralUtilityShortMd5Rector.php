<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Rector\v11\v4;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\AstResolver;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/11.4/Deprecation-94684-GeneralUtilityShortMD5.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v11\v4\UseNativeFunctionInsteadOfGeneralUtilityShortMd5Rector\UseNativeFunctionInsteadOfGeneralUtilityShortMd5RectorTest
 */
final class UseNativeFunctionInsteadOfGeneralUtilityShortMd5Rector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\AstResolver
     */
    private $astResolver;
    public function __construct(\Rector\Core\PhpParser\AstResolver $astResolver)
    {
        $this->astResolver = $astResolver;
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new \PHPStan\Type\ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'))) {
            return null;
        }
        if (!$this->nodeNameResolver->isName($node->name, 'shortMD5')) {
            return null;
        }
        $lengthValue = $this->extractLengthValue($node);
        $arguments = [$this->nodeFactory->createFuncCall('md5', [$node->args[0]->value]), 0, $lengthValue];
        return $this->nodeFactory->createFuncCall('substr', $arguments);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use php native function instead of GeneralUtility::shortMd5', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Utility\GeneralUtility;

$length = 10;
$input = 'value';

$shortMd5 = GeneralUtility::shortMD5($input, $length);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$length = 10;
$input = 'value';

$shortMd5 = substr(md5($input), 0, $length);
CODE_SAMPLE
)]);
    }
    /**
     * @return mixed
     */
    private function extractLengthValue(\PhpParser\Node\Expr\StaticCall $staticCall)
    {
        $classMethod = $this->astResolver->resolveClassMethodFromCall($staticCall);
        $lengthValue = 10;
        if (isset($staticCall->args[1])) {
            $lengthValue = $staticCall->args[1]->value;
        } elseif ($classMethod instanceof \PhpParser\Node\Stmt\ClassMethod && null !== $classMethod->params[1]->default) {
            $lengthValue = $this->valueResolver->getValue($classMethod->params[1]->default);
        }
        return $lengthValue;
    }
}
