<?php

declare (strict_types=1);
namespace Rector\Nette\Rector\Neon;

use RectorPrefix20211020\Nette\Utils\Strings;
use Rector\Nette\Contract\Rector\NeonRectorInterface;
use Rector\Renaming\Collector\MethodCallRenameCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Nette\Tests\Rector\Neon\RenameMethodNeonRector\RenameMethodNeonRectorTest
 */
final class RenameMethodNeonRector implements \Rector\Nette\Contract\Rector\NeonRectorInterface
{
    /**
     * @var \Rector\Renaming\Collector\MethodCallRenameCollector
     */
    private $methodCallRenameCollector;
    public function __construct(\Rector\Renaming\Collector\MethodCallRenameCollector $methodCallRenameCollector)
    {
        $this->methodCallRenameCollector = $methodCallRenameCollector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Renames method calls in NEON configs', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
)]);
    }
    public function changeContent(string $content) : string
    {
        foreach ($this->methodCallRenameCollector->getMethodCallRenames() as $methodCallRename) {
            $oldObjectType = $methodCallRename->getOldObjectType();
            $objectClassName = $oldObjectType->getClassName();
            $className = \str_replace('\\', '\\\\', $objectClassName);
            $oldMethodName = $methodCallRename->getOldMethod();
            $newMethodName = $methodCallRename->getNewMethod();
            $pattern = '#\\n(.*?)(class|factory): ' . $className . '(\\n|\\((.*?)\\)\\n)\\1setup:(.*?)- ' . $oldMethodName . '\\(#s';
            if (\RectorPrefix20211020\Nette\Utils\Strings::match($content, $pattern)) {
                $content = \str_replace($oldMethodName . '(', $newMethodName . '(', $content);
            }
        }
        return $content;
    }
}
