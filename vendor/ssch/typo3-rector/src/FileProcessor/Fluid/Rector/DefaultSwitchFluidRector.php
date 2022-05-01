<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\FileProcessor\Fluid\Rector;

use RectorPrefix20220501\Nette\Utils\Strings;
use Rector\Core\ValueObject\Application\File;
use Ssch\TYPO3Rector\Contract\FileProcessor\Fluid\Rector\FluidRectorInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://forge.typo3.org/issues/73068
 */
final class DefaultSwitchFluidRector implements \Ssch\TYPO3Rector\Contract\FileProcessor\Fluid\Rector\FluidRectorInterface
{
    /**
     * @var string
     */
    private const PATTERN = '#<f:case default="(1|true)">(.*)<\\/f:case>#imsU';
    /**
     * @var string
     */
    private const REPLACEMENT = '<f:defaultCase>$2</f:defaultCase>';
    public function transform(\Rector\Core\ValueObject\Application\File $file) : void
    {
        $content = $file->getFileContent();
        $content = \RectorPrefix20220501\Nette\Utils\Strings::replace($content, self::PATTERN, self::REPLACEMENT);
        $file->changeFileContent($content);
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Use <f:defaultCase> instead of <f:case default="1">', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
<f:switch expression="{someVariable}">
    <f:case value="...">...</f:case>
    <f:case value="...">...</f:case>
    <f:case value="...">...</f:case>
    <f:case default="1">...</f:case>
</f:switch>
CODE_SAMPLE
, <<<'CODE_SAMPLE'
<f:switch expression="{someVariable}">
    <f:case value="...">...</f:case>
    <f:case value="...">...</f:case>
    <f:case value="...">...</f:case>
    <f:defaultCase>...</f:defaultCase>
</f:switch>
CODE_SAMPLE
)]);
    }
}
