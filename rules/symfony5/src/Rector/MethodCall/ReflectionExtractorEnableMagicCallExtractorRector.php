<?php

declare(strict_types=1);

namespace Rector\Symfony5\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\BinaryOp\BitwiseOr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://github.com/symfony/symfony/blob/5.x/UPGRADE-5.2.md#propertyinfo
 * @see \Rector\Symfony5\Tests\Rector\MethodCall\ReflectionExtractorEnableMagicCallExtractorRector\ReflectionExtractorEnableMagicCallExtractorRectorTest
 */
final class ReflectionExtractorEnableMagicCallExtractorRector extends AbstractRector
{
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

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Migrates from deprecated enable_magic_call_extraction context option in ReflectionExtractor',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
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
                    ,
                    <<<'CODE_SAMPLE'
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
                ),
            ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $contextOptionValue = $this->getContextOptionValue($node);
        if ($contextOptionValue === null) {
            return null;
        }

        /** @var Array_ $contextOptions */
        $contextOptions = $node->args[2]->value;
        $contextOptions->items[] = new ArrayItem(
            $this->prepareEnableMagicMethodsExtractionFlags($contextOptionValue),
            new String_(self::NEW_OPTION_NAME),
        );

        return $node;
    }

    private function shouldSkip(MethodCall $methodCall): bool
    {
        if (! $this->isObjectType($methodCall->var, 'Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor')) {
            return true;
        }

        if (! $this->isNames($methodCall->name, self::METHODS_WITH_OPTION)) {
            return true;
        }

        if (count($methodCall->args) < 3) {
            return true;
        }

        /** @var Array_ $contextOptions */
        $contextOptions = $methodCall->args[2]->value;

        return $contextOptions->items === [];
    }

    private function getContextOptionValue(MethodCall $methodCall): ?bool
    {
        /** @var Array_ $contextOptions */
        $contextOptions = $methodCall->args[2]->value;

        $contextOptionValue = null;

        foreach ($contextOptions->items as $index => $arrayItem) {
            if (! $arrayItem instanceof ArrayItem) {
                continue;
            }

            if (! $arrayItem->key instanceof String_) {
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

    private function prepareEnableMagicMethodsExtractionFlags(bool $enableMagicCallExtractionValue): BitwiseOr
    {
        $magicGetClassConstFetch = $this->nodeFactory->createClassConstFetch(
            'Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor',
            'MAGIC_GET'
        );
        $magicSetClassConstFetch = $this->nodeFactory->createClassConstFetch(
            'Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor',
            'MAGIC_SET'
        );

        if (! $enableMagicCallExtractionValue) {
            return new BitwiseOr($magicGetClassConstFetch, $magicSetClassConstFetch);
        }

        return new BitwiseOr(
            new BitwiseOr(
                $this->nodeFactory->createClassConstFetch(
                    'Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor',
                    'MAGIC_CALL'
                ),
                $magicGetClassConstFetch,
            ),
            $magicSetClassConstFetch,
        );
    }
}
