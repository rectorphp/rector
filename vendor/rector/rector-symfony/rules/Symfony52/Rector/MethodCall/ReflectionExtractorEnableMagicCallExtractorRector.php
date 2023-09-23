<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony52\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\ObjectType;
use Rector\Core\PhpParser\Node\Value\ValueResolver;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#propertyinfo
 * @see \Rector\Symfony\Tests\Symfony52\Rector\MethodCall\ReflectionExtractorEnableMagicCallExtractorRector\ReflectionExtractorEnableMagicCallExtractorRectorTest
 */
final class ReflectionExtractorEnableMagicCallExtractorRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var string
     */
    private const OLD_OPTION_NAME = 'enable_magic_call_extraction';
    /**
     * @var string
     */
    private const NEW_OPTION_NAME = 'enable_magic_methods_extraction';
    /**
     * @var string[]
     */
    private const METHODS_WITH_OPTION = ['getWriteInfo', 'getReadInfo'];
    public function __construct(ValueResolver $valueResolver)
    {
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Migrates from deprecated enable_magic_call_extraction context option in ReflectionExtractor', [new CodeSample(<<<'CODE_SAMPLE'
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

class SomeClass
{
    public function run()
    {
        $reflectionExtractor = new ReflectionExtractor();
        $readInfo = $reflectionExtractor->getReadInfo(Dummy::class, 'bar', [
            'enable_magic_call_extraction' => true,
        ]);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;

class SomeClass
{
    public function run()
    {
        $reflectionExtractor = new ReflectionExtractor();
        $readInfo = $reflectionExtractor->getReadInfo(Dummy::class, 'bar', [
            'enable_magic_methods_extraction' => ReflectionExtractor::MAGIC_CALL | ReflectionExtractor::MAGIC_GET | ReflectionExtractor::MAGIC_SET,
        ]);
    }
}
CODE_SAMPLE
)]);
    }
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
        $contextOptionValue = $this->getContextOptionValue($node);
        if ($contextOptionValue === null) {
            return null;
        }
        $thirdArg = $node->args[2];
        if (!$thirdArg instanceof Arg) {
            return null;
        }
        $contextOptions = $thirdArg->value;
        if (!$contextOptions instanceof Array_) {
            return null;
        }
        $contextOptions->items[] = new ArrayItem($this->prepareEnableMagicMethodsExtractionFlags($contextOptionValue), new String_(self::NEW_OPTION_NAME));
        return $node;
    }
    private function shouldSkip(MethodCall $methodCall) : bool
    {
        if (!$this->isNames($methodCall->name, self::METHODS_WITH_OPTION)) {
            return \true;
        }
        $reflectionExtractorObjectType = new ObjectType('Symfony\\Component\\PropertyInfo\\Extractor\\ReflectionExtractor');
        if (!$this->isObjectType($methodCall->var, $reflectionExtractorObjectType)) {
            return \true;
        }
        if (\count($methodCall->args) < 3) {
            return \true;
        }
        $thirdArg = $methodCall->args[2];
        if (!$thirdArg instanceof Arg) {
            return \true;
        }
        $contextOptions = $thirdArg->value;
        if (!$contextOptions instanceof Array_) {
            return \true;
        }
        return $contextOptions->items === [];
    }
    private function getContextOptionValue(MethodCall $methodCall) : ?bool
    {
        $thirdArg = $methodCall->args[2];
        if (!$thirdArg instanceof Arg) {
            return null;
        }
        $contextOptions = $thirdArg->value;
        if (!$contextOptions instanceof Array_) {
            return null;
        }
        $contextOptionValue = null;
        foreach ($contextOptions->items as $index => $arrayItem) {
            if (!$arrayItem instanceof ArrayItem) {
                continue;
            }
            if (!$arrayItem->key instanceof String_) {
                continue;
            }
            if ($arrayItem->key->value !== self::OLD_OPTION_NAME) {
                continue;
            }
            $contextOptionValue = $this->valueResolver->isTrue($arrayItem->value);
            unset($contextOptions->items[$index]);
        }
        return $contextOptionValue;
    }
    private function prepareEnableMagicMethodsExtractionFlags(bool $enableMagicCallExtractionValue) : BitwiseOr
    {
        $magicGetClassConstFetch = $this->nodeFactory->createClassConstFetch('Symfony\\Component\\PropertyInfo\\Extractor\\ReflectionExtractor', 'MAGIC_GET');
        $magicSetClassConstFetch = $this->nodeFactory->createClassConstFetch('Symfony\\Component\\PropertyInfo\\Extractor\\ReflectionExtractor', 'MAGIC_SET');
        if (!$enableMagicCallExtractionValue) {
            return new BitwiseOr($magicGetClassConstFetch, $magicSetClassConstFetch);
        }
        $magicCallClassConstFetch = $this->nodeFactory->createClassConstFetch('Symfony\\Component\\PropertyInfo\\Extractor\\ReflectionExtractor', 'MAGIC_CALL');
        return new BitwiseOr(new BitwiseOr($magicCallClassConstFetch, $magicGetClassConstFetch), $magicSetClassConstFetch);
    }
}
