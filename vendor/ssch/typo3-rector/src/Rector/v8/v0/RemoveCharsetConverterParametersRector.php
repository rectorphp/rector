<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v0;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.0/Breaking-74031-CharsetConverterParametersRemoved.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v0\RemoveCharsetConverterParametersRector\RemoveCharsetConverterParametersRectorTest
 */
final class RemoveCharsetConverterParametersRector extends AbstractRector
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
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($node, new ObjectType('TYPO3\\CMS\\Core\\Charset\\CharsetConverter'))) {
            return null;
        }
        if (!$this->isNames($node->name, ['entities_to_utf8', 'utf8_to_numberarray'])) {
            return null;
        }
        $node->args = [$node->args[0]];
        return $node;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove CharsetConvertParameters', [new CodeSample(<<<'CODE_SAMPLE'
$charsetConvert = GeneralUtility::makeInstance(CharsetConverter::class);
$charsetConvert->entities_to_utf8('string', false);
$charsetConvert->utf8_to_numberarray('string', false, false);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$charsetConvert = GeneralUtility::makeInstance(CharsetConverter::class);
$charsetConvert->entities_to_utf8('string');
$charsetConvert->utf8_to_numberarray('string');
CODE_SAMPLE
)]);
    }
}
