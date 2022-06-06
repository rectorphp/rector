<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\Rector\v8\v5;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr\ConstFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\FuncCall;
use RectorPrefix20220606\PhpParser\Node\Expr\MethodCall;
use RectorPrefix20220606\PhpParser\Node\Name;
use RectorPrefix20220606\PHPStan\Type\ObjectType;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://docs.typo3.org/c/typo3/cms-core/master/en-us/Changelog/8.5/Deprecation-78670-DeprecatedCharsetConverterMethods.html
 * @see \Ssch\TYPO3Rector\Tests\Rector\v8\v5\CharsetConverterToMultiByteFunctionsRector\CharsetConverterToMultiByteFunctionsRectorTest
 */
final class CharsetConverterToMultiByteFunctionsRector extends AbstractRector
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
        if ($this->shouldSkip($node)) {
            return null;
        }
        $nodeName = $this->getName($node->name);
        if (null === $nodeName) {
            return null;
        }
        if ('strlen' === $nodeName) {
            return $this->toMultiByteStrlen($node);
        }
        if ('convCapitalize' === $nodeName) {
            return $this->toMultiByteConvertCase($node);
        }
        if ('substr' === $nodeName) {
            return $this->toMultiByteSubstr($node);
        }
        if ('conv_case' === $nodeName) {
            return $this->toMultiByteLowerUpperCase($node);
        }
        if ('utf8_strpos' === $nodeName) {
            return $this->toMultiByteStrPos($node);
        }
        if ('utf8_strrpos' === $nodeName) {
            return $this->toMultiByteStrrPos($node);
        }
        return null;
    }
    /**
     * @codeCoverageIgnore
     */
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Move from CharsetConverter methods to mb_string functions', [new CodeSample(<<<'CODE_SAMPLE'
use TYPO3\CMS\Core\Charset\CharsetConverter;
use TYPO3\CMS\Core\Utility\GeneralUtility;

$charsetConverter = GeneralUtility::makeInstance(CharsetConverter::class);
$charsetConverter->strlen('utf-8', 'string');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
mb_strlen('string', 'utf-8');
CODE_SAMPLE
)]);
    }
    private function shouldSkip(MethodCall $methodCall) : bool
    {
        if (!$this->nodeTypeResolver->isMethodStaticCallOrClassMethodObjectType($methodCall, new ObjectType('TYPO3\\CMS\\Core\\Charset\\CharsetConverter'))) {
            return \true;
        }
        return !$this->isNames($methodCall->name, ['strlen', 'convCapitalize', 'substr', 'conv_case', 'utf8_strpos', 'utf8_strrpos']);
    }
    private function toMultiByteConvertCase(MethodCall $methodCall) : FuncCall
    {
        return $this->nodeFactory->createFuncCall('mb_convert_case', [$methodCall->args[1], new ConstFetch(new Name('MB_CASE_TITLE')), $methodCall->args[0]]);
    }
    private function toMultiByteSubstr(MethodCall $methodCall) : FuncCall
    {
        $start = $methodCall->args[2] ?? $this->nodeFactory->createArg(0);
        $length = $methodCall->args[3] ?? $this->nodeFactory->createNull();
        return $this->nodeFactory->createFuncCall('mb_substr', [$methodCall->args[1], $start, $length, $methodCall->args[0]]);
    }
    private function toMultiByteLowerUpperCase(MethodCall $methodCall) : FuncCall
    {
        $mbMethodCall = 'toLower' === $this->valueResolver->getValue($methodCall->args[2]->value) ? 'mb_strtolower' : 'mb_strtoupper';
        return $this->nodeFactory->createFuncCall($mbMethodCall, [$methodCall->args[1], $methodCall->args[0]]);
    }
    private function toMultiByteStrPos(MethodCall $methodCall) : FuncCall
    {
        $offset = $methodCall->args[2] ?? $this->nodeFactory->createArg(0);
        return $this->nodeFactory->createFuncCall('mb_strpos', [$methodCall->args[0], $methodCall->args[1], $offset, $this->nodeFactory->createArg('utf-8')]);
    }
    private function toMultiByteStrrPos(MethodCall $methodCall) : FuncCall
    {
        return $this->nodeFactory->createFuncCall('mb_strrpos', [$methodCall->args[0], $methodCall->args[1], $this->nodeFactory->createArg('utf-8')]);
    }
    private function toMultiByteStrlen(MethodCall $methodCall) : FuncCall
    {
        return $this->nodeFactory->createFuncCall('mb_strlen', \array_reverse($methodCall->args));
    }
}
