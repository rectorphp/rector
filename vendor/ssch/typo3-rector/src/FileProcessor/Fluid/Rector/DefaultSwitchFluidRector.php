<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Ssch\TYPO3Rector\FileProcessor\Fluid\Rector;

use RectorPrefix20220606\Nette\Utils\Strings;
use RectorPrefix20220606\Rector\Core\ValueObject\Application\File;
use RectorPrefix20220606\Ssch\TYPO3Rector\Contract\FileProcessor\Fluid\Rector\FluidRectorInterface;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://forge.typo3.org/issues/73068
 */
final class DefaultSwitchFluidRector implements FluidRectorInterface
{
    /**
     * @var string
     */
    private const PATTERN = '#<f:case default="(1|true)">(.*)<\\/f:case>#imsU';
    /**
     * @var string
     */
    private const REPLACEMENT = '<f:defaultCase>$2</f:defaultCase>';
    public function transform(File $file) : void
    {
        $content = $file->getFileContent();
        $content = Strings::replace($content, self::PATTERN, self::REPLACEMENT);
        $file->changeFileContent($content);
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use <f:defaultCase> instead of <f:case default="1">', [new CodeSample(<<<'CODE_SAMPLE'
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
