<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\Neon;

use RectorPrefix20210514\Nette\Utils\Strings;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Nette\Contract\Rector\NeonRectorInterface;
use Rector\Renaming\ValueObject\MethodCallRename;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix20210514\Webmozart\Assert\Assert;
/**
 * @see \Rector\Nette\Tests\Rector\Neon\RenameMethodNeonRector\RenameMethodNeonRectorTest
 */
final class RenameMethodNeonRector implements \Rector\Nette\Contract\Rector\NeonRectorInterface, \Rector\Core\Contract\Rector\ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const RENAME_METHODS = 'rename_methods';
    /**
     * @var MethodCallRename[]
     */
    private $methodCallRenames = [];
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Renames method calls in NEON configs', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
services:
    -
        class: SomeClass
        setup:
            - oldCall
CODE_SAMPLE
, <<<'CODE_SAMPLE'
services:
    -
        class: SomeClass
        setup:
            - newCall
CODE_SAMPLE
, [self::RENAME_METHODS => [new \Rector\Renaming\ValueObject\MethodCallRename('SomeClass', 'oldCall', 'newCall')]])]);
    }
    /**
     * @param array<string, MethodCallRename[]> $configuration
     */
    public function configure(array $configuration) : void
    {
        $methodCallRenames = $configuration[self::RENAME_METHODS] ?? [];
        \RectorPrefix20210514\Webmozart\Assert\Assert::allIsInstanceOf($methodCallRenames, \Rector\Renaming\ValueObject\MethodCallRename::class);
        $this->methodCallRenames = $methodCallRenames;
    }
    public function changeContent(string $content) : string
    {
        foreach ($this->methodCallRenames as $methodCallRename) {
            $oldObjectType = $methodCallRename->getOldObjectType();
            $objectClassName = $oldObjectType->getClassName();
            $className = \str_replace('\\', '\\\\', $objectClassName);
            $oldMethodName = $methodCallRename->getOldMethod();
            $newMethodName = $methodCallRename->getNewMethod();
            $pattern = '#\\n(.*?)(class|factory): ' . $className . '(\\n|\\((.*?)\\)\\n)\\1setup:(.*?)- ' . $oldMethodName . '\\(#s';
            if (\RectorPrefix20210514\Nette\Utils\Strings::match($content, $pattern)) {
                $content = \str_replace($oldMethodName . '(', $newMethodName . '(', $content);
            }
        }
        return $content;
    }
}
