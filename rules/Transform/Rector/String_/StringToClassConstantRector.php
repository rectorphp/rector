<?php

declare (strict_types=1);
namespace Rector\Transform\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Transform\ValueObject\StringToClassConstant;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20211020\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Transform\Rector\String_\StringToClassConstantRector\StringToClassConstantRectorTest
 */
final class StringToClassConstantRector extends \Rector\Core\Rector\AbstractRector implements \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const STRINGS_TO_CLASS_CONSTANTS = 'strings_to_class_constants';
    /**
     * @var StringToClassConstant[]
     */
    private $stringsToClassConstants = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Changes strings to specific constants', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
final class SomeSubscriber
{
    public static function getSubscribedEvents()
    {
        return ['compiler.post_dump' => 'compile'];
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeSubscriber
{
    public static function getSubscribedEvents()
    {
        return [\Yet\AnotherClass::CONSTANT => 'compile'];
    }
}
CODE_SAMPLE
, [self::STRINGS_TO_CLASS_CONSTANTS => [new \Rector\Transform\ValueObject\StringToClassConstant('compiler.post_dump', 'Yet\\AnotherClass', 'CONSTANT')]])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Scalar\String_::class];
    }
    /**
     * @param String_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        foreach ($this->stringsToClassConstants as $stringToClassConstant) {
            if (!$this->valueResolver->isValue($node, $stringToClassConstant->getString())) {
                continue;
            }
            return $this->nodeFactory->createClassConstFetch($stringToClassConstant->getClass(), $stringToClassConstant->getConstant());
        }
        return $node;
    }
    /**
     * @param array<string, StringToClassConstant[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $stringToClassConstants = $configuration[self::STRINGS_TO_CLASS_CONSTANTS] ?? [];
        \RectorPrefix20211020\Webmozart\Assert\Assert::allIsInstanceOf($stringToClassConstants, \Rector\Transform\ValueObject\StringToClassConstant::class);
        $this->stringsToClassConstants = $stringToClassConstants;
    }
}
