<?php

declare (strict_types=1);
namespace Rector\RectorGenerator\Generator;

use Rector\RectorGenerator\Finder\TemplateFinder;
use Rector\RectorGenerator\Guard\OverrideGuard;
use Rector\RectorGenerator\TemplateVariablesFactory;
use Rector\RectorGenerator\ValueObject\RectorRecipe;
use RectorPrefix20211231\Symfony\Component\Console\Style\SymfonyStyle;
/**
 * @see \Rector\RectorGenerator\Tests\RectorGenerator\RectorGeneratorTest
 */
final class RectorGenerator
{
    /**
     * @readonly
     * @var \Rector\RectorGenerator\Finder\TemplateFinder
     */
    private $templateFinder;
    /**
     * @readonly
     * @var \Rector\RectorGenerator\TemplateVariablesFactory
     */
    private $templateVariablesFactory;
    /**
     * @readonly
     * @var \Rector\RectorGenerator\Generator\FileGenerator
     */
    private $fileGenerator;
    /**
     * @readonly
     * @var \Rector\RectorGenerator\Guard\OverrideGuard
     */
    private $overrideGuard;
    /**
     * @readonly
     * @var \Symfony\Component\Console\Style\SymfonyStyle
     */
    private $symfonyStyle;
    public function __construct(\Rector\RectorGenerator\Finder\TemplateFinder $templateFinder, \Rector\RectorGenerator\TemplateVariablesFactory $templateVariablesFactory, \Rector\RectorGenerator\Generator\FileGenerator $fileGenerator, \Rector\RectorGenerator\Guard\OverrideGuard $overrideGuard, \RectorPrefix20211231\Symfony\Component\Console\Style\SymfonyStyle $symfonyStyle)
    {
        $this->templateFinder = $templateFinder;
        $this->templateVariablesFactory = $templateVariablesFactory;
        $this->fileGenerator = $fileGenerator;
        $this->overrideGuard = $overrideGuard;
        $this->symfonyStyle = $symfonyStyle;
    }
    /**
     * @return string[]
     */
    public function generate(\Rector\RectorGenerator\ValueObject\RectorRecipe $rectorRecipe, string $destinationDirectory) : array
    {
        // generate and compare
        $templateFileInfos = $this->templateFinder->find($rectorRecipe);
        $templateVariables = $this->templateVariablesFactory->createFromRectorRecipe($rectorRecipe);
        $isUnwantedOverride = $this->overrideGuard->isUnwantedOverride($templateFileInfos, $templateVariables, $rectorRecipe, $destinationDirectory);
        if ($isUnwantedOverride) {
            $this->symfonyStyle->warning('No files were changed');
            return [];
        }
        return $this->fileGenerator->generateFiles($templateFileInfos, $templateVariables, $rectorRecipe, $destinationDirectory);
    }
}
