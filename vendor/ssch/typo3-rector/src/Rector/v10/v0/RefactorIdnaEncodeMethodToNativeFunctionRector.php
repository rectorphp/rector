<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v10\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\BinaryOp\Concat;
use RectorPrefix20220606\PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticCall;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PhpParser\Node\Scalar\String_;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/10.0/Deprecation-87894-GeneralUtilityidnaEncode.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v10\v0\RefactorIdnaEncodeMethodToNativeFunctionRector\RefactorIdnaEncodeMethodToNativeFunctionRectorTest
 */
final class RefactorIdnaEncodeMethodToNativeFunctionRector extends AbstractRector
{
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Core\\Utility\\GeneralUtility'))) {
            return null;
        }
        if (!$this->isName($node->name, 'idnaEncode')) {
            return null;
        }
        $arguments = $node->args;
        if ([] === $arguments) {
            return null;
        }
        $firstArgumentValue = $this->valueResolver->getValue($arguments[0]->value);
        if (!\is_string($firstArgumentValue)) {
            return null;
        }
        if (\strpos($firstArgumentValue, '@') === \false) {
            return $this->refactorToNativeFunction($firstArgumentValue);
        }
        return $this->refactorToEmailConcatWithNativeFunction($firstArgumentValue);
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use native function idn_to_ascii instead of GeneralUtility::idnaEncode', [new CodeSample(<<<'CODE_SAMPLE'
$domain = GeneralUtility::idnaEncode('domain.com');
$email = GeneralUtility::idnaEncode('email@domain.com');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$domain = idn_to_ascii('domain.com', IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
$email = 'email@' . idn_to_ascii('domain.com', IDNA_DEFAULT, INTL_IDNA_VARIANT_UTS46);
CODE_SAMPLE
)]);
    }
    private function refactorToNativeFunction(string $value) : FuncCall
    {
        return $this->nodeFactory->createFuncCall('idn_to_ascii', [new String_($value), new ConstFetch(new Name('IDNA_DEFAULT')), new ConstFetch(new Name('INTL_IDNA_VARIANT_UTS46'))]);
    }
    private function refactorToEmailConcatWithNativeFunction(string $value) : Concat
    {
        [$email, $domain] = \explode('@', $value, 2);
        return new Concat(new String_($email . '@'), $this->refactorToNativeFunction($domain));
    }
}
