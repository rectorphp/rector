<?php

declare(strict_types=1);

namespace Rector\Generic\Rector\String_;

use PhpParser\Node;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\ConfiguredCodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;

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
     * @var string[][]
     */
    private $stringsToClassConstants = [];

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Changes strings to specific constants', [
            new ConfiguredCodeSample(
                <<<'PHP'
final class SomeSubscriber
{
    public static function getSubscribedEvents()
    {
        return ['compiler.post_dump' => 'compile'];
    }
}
PHP
                ,
                <<<'PHP'
final class SomeSubscriber
{
    public static function getSubscribedEvents()
    {
        return [\Yet\AnotherClass::CONSTANT => 'compile'];
    }
}
PHP
                ,
                [
                    self::STRINGS_TO_CLASS_CONSTANTS => [
                        'compiler.post_dump' => ['Yet\AnotherClass', 'CONSTANT'],
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
        foreach ($this->stringsToClassConstants as $string => $classConstant) {
            if (! $this->isValue($node, $string)) {
                continue;
            }

            return new ClassConstFetch(new FullyQualified($classConstant[0]), $classConstant[1]);
        }

        return $node;
    }

    public function configure(array $configuration): void
    {
        $this->stringsToClassConstants = $configuration[self::STRINGS_TO_CLASS_CONSTANTS] ?? [];
    }
}
