<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Generator;

use Rector\RectorGenerator\Finder\TemplateFinder;
use Rector\RectorGenerator\TemplateVariablesFactory;
use Rector\RectorGenerator\ValueObject\RectorRecipe;

final class RectorRecipeGenerator
{
    /**
     * @var TemplateFinder
     */
    private $templateFinder;

    /**
     * @var TemplateVariablesFactory
     */
    private $templateVariablesFactory;

    /**
     * @var FileGenerator
     */
    private $fileGenerator;

    public function __construct(
        TemplateFinder $templateFinder,
        TemplateVariablesFactory $templateVariablesFactory,
        FileGenerator $fileGenerator
    ) {
        $this->templateFinder = $templateFinder;
        $this->templateVariablesFactory = $templateVariablesFactory;
        $this->fileGenerator = $fileGenerator;
    }

    public function generate(RectorRecipe $rectorRecipe, string $destinationDirectory): void
    {
        // generate and compare
        $templateFileInfos = $this->templateFinder->find($rectorRecipe);
        $templateVariables = $this->templateVariablesFactory->createFromRectorRecipe($rectorRecipe);

        $this->fileGenerator->generateFiles(
            $templateFileInfos,
            $templateVariables,
            $rectorRecipe,
            $destinationDirectory
        );
    }
}
