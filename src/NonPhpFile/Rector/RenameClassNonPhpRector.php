<?php

declare (strict_types=1);
namespace Rector\Core\NonPhpFile\Rector;

use RectorPrefix20211020\Nette\Utils\Strings;
use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Contract\Rector\NonPhpRectorInterface;
use Rector\PostRector\Contract\Rector\ComplementaryRectorInterface;
use Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
final class RenameClassNonPhpRector implements \Rector\Core\Contract\Rector\NonPhpRectorInterface, \Symplify\RuleDocGenerator\Contract\ConfigurableRuleInterface, \Rector\Core\Contract\Rector\ConfigurableRectorInterface, \Rector\PostRector\Contract\Rector\ComplementaryRectorInterface
{
    /**
     * @var string
     */
    public const RENAME_CLASSES = 'rename_classes';
    /**
     * @see https://regex101.com/r/HKUFJD/7
     * for "?<!" @see https://stackoverflow.com/a/3735908/1348344
     * @var string
     */
    private const STANDALONE_CLASS_PREFIX_REGEX = '#((?<!(\\\\|"|\\>|\\.|\'))|(?<extra_space>\\s+\\\\))';
    /**
     * @see https://regex101.com/r/HKUFJD/5
     * @see https://stackoverflow.com/a/3926546/1348344
     * @var string
     */
    private const STANDALONE_CLASS_SUFFIX_REGEX = '(?=::)#';
    /**
     * @var array<string, string>
     */
    private $renameClasses = [];
    /**
     * @var \Rector\Core\Configuration\RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;
    public function __construct(\Rector\Core\Configuration\RenamedClassesDataCollector $renamedClassesDataCollector)
    {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change class names and just renamed classes in non-PHP files, NEON, YAML, TWIG, LATTE, blade etc. mostly with regular expressions', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample(<<<'CODE_SAMPLE'
services:
    - SomeOldClass
CODE_SAMPLE
, <<<'CODE_SAMPLE'
services:
    - SomeNewClass
CODE_SAMPLE
, [self::RENAME_CLASSES => ['SomeOldClass' => 'SomeNewClass']])]);
    }
    public function refactorFileContent(string $fileContent) : string
    {
        $classRenames = $this->getRenameClasses();
        return $this->renameClasses($fileContent, $classRenames);
    }
    /**
     * @param array<string, array<string, string>> $configuration
     */
    public function configure(array $configuration) : void
    {
        $this->renameClasses = $configuration[self::RENAME_CLASSES] ?? [];
    }
    /**
     * @param array<string, string> $classRenames
     */
    private function renameClasses(string $newContent, array $classRenames) : string
    {
        $classRenames = $this->addDoubleSlahed($classRenames);
        foreach ($classRenames as $oldClass => $newClass) {
            // the old class is without slashes, it can make mess as similar to a word in the text, so we have to be more strict about it
            $oldClassRegex = $this->createOldClassRegex($oldClass);
            $newContent = \RectorPrefix20211020\Nette\Utils\Strings::replace($newContent, $oldClassRegex, function (array $match) use($newClass) : string {
                return ($match['extra_space'] ?? '') . $newClass;
            });
        }
        return $newContent;
    }
    /**
     * Process with double quotes too, e.g. in twig
     *
     * @param array<string, string> $classRenames
     * @return array<string, string>
     */
    private function addDoubleSlahed(array $classRenames) : array
    {
        foreach ($classRenames as $oldClass => $newClass) {
            // to prevent no slash override
            if (\strpos($oldClass, '\\') === \false) {
                continue;
            }
            $doubleSlashOldClass = \str_replace('\\', '\\\\', $oldClass);
            $doubleSlashNewClass = \str_replace('\\', '\\\\', $newClass);
            $classRenames[$doubleSlashOldClass] = $doubleSlashNewClass;
        }
        return $classRenames;
    }
    /**
     * @return array<string, string>
     */
    private function getRenameClasses() : array
    {
        return \array_merge($this->renameClasses, $this->renamedClassesDataCollector->getOldToNewClasses());
    }
    private function createOldClassRegex(string $oldClass) : string
    {
        if (\strpos($oldClass, '\\') === \false) {
            return self::STANDALONE_CLASS_PREFIX_REGEX . \preg_quote($oldClass, '#') . self::STANDALONE_CLASS_SUFFIX_REGEX;
        }
        return '#' . \preg_quote($oldClass, '#') . '#';
    }
}
