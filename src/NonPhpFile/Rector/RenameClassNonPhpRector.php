<?php

declare(strict_types=1);

namespace Rector\Core\NonPhpFile\Rector;

use Rector\Core\Configuration\RenamedClassesDataCollector;
use Rector\Core\Contract\Rector\NonPhpRectorInterface;
use Rector\Core\NonPhpFile\NonPhpFileClassRenamer;
use Rector\PSR4\Collector\RenamedClassesCollector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

final class RenameClassNonPhpRector implements NonPhpRectorInterface
{
    /**
     * @var RenamedClassesDataCollector
     */
    private $renamedClassesDataCollector;

    /**
     * @var RenamedClassesCollector
     */
    private $renamedClassesCollector;

    /**
     * @var NonPhpFileClassRenamer
     */
    private $nonPhpFileClassRenamer;

    public function __construct(
        RenamedClassesDataCollector $renamedClassesDataCollector,
        RenamedClassesCollector $renamedClassesCollector,
        NonPhpFileClassRenamer $nonPhpFileClassRenamer
    ) {
        $this->renamedClassesDataCollector = $renamedClassesDataCollector;
        $this->renamedClassesCollector = $renamedClassesCollector;
        $this->nonPhpFileClassRenamer = $nonPhpFileClassRenamer;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Change class names and just renamed classes in non-PHP files, NEON, YAML, TWIG, LATTE, blade etc. mostly with regular expressions', [
                new CodeSample(
<<<'CODE_SAMPLE'
services:
    - SomeOldClass
CODE_SAMPLE
                , <<<'CODE_SAMPLE'
services:
    - SomeNewClass
CODE_SAMPLE),
            ]);
    }

    public function refactorFileContent(string $fileContent): string
    {
        $classRenames = array_merge(
            $this->renamedClassesDataCollector->getOldToNewClasses(),
            $this->renamedClassesCollector->getOldToNewClasses()
        );

        return $this->nonPhpFileClassRenamer->renameClasses($fileContent, $classRenames);
    }
}
