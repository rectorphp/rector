<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\Generic\ValueObject\StringToClassConstant;
use Webmozart\Assert\Assert;

/**
 * @see \Rector\Generic\Tests\Rector\String_\StringToClassConstantRector\StringToClassConstantRectorTest
 */
final class StringToClassConstantRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const STRINGS_TO_CLASS_CONSTANTS = 'strings_to_class_constants';

    /**
     * @var StringToClassConstant[]
     */
    private $stringsToClassConstants = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes strings to specific constants', [
            new ConfiguredCodeSample(
                <<<'CODE_SAMPLE'
final class SomeSubscriber
{
    public static function getSubscribedEvents()
    {
        return ['compiler.post_dump' => 'compile'];
    }
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
final class SomeSubscriber
{
    public static function getSubscribedEvents()
    {
        return [\Yet\AnotherClass::CONSTANT => 'compile'];
    }
}
CODE_SAMPLE
                ,
                [
                    self::STRINGS_TO_CLASS_CONSTANTS => [
                        new StringToClassConstant('compiler.post_dump', 'Yet\AnotherClass', 'CONSTANT'),
                    ],
                ]
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [String_::class];
    }

    /**
     * @param String_ $node
     */
    public function refactor(Node $node): ?Node
    {
        foreach ($this->stringsToClassConstants as $stringToClassConstant) {
            if (! $this->isValue($node, $stringToClassConstant->getString())) {
                continue;
            }

            return $this->createClassConstFetch(
                $stringToClassConstant->getClass(),
                $stringToClassConstant->getConstant()
            );
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $stringToClassConstants = $configuration[self::STRINGS_TO_CLASS_CONSTANTS] ?? [];
        Assert::allIsInstanceOf($stringToClassConstants, StringToClassConstant::class);
        $this->stringsToClassConstants = $stringToClassConstants;
    }
}
