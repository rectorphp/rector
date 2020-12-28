<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\ValueObjectFactory;

use Rector\RectorGenerator\Provider\NodeTypesProvider;
use Rector\RectorGenerator\Provider\PackageNamesProvider;
use Rector\RectorGenerator\Provider\SetsListProvider;
use Rector\RectorGenerator\ValueObject\RectorRecipe;
use Rector\Set\ValueObject\SetList;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @see \Rector\RectorGenerator\Tests\Provider\RectorRecipeInteractiveProviderTest
 */
final class RectorRecipeInteractiveFactory
{
    /**
     * @var string
     */
    public const EXAMPLE_CODE_BEFORE = <<<'CODE_SAMPLE'
<?php

class SomeClass
{
    public function run()
    {
        $this->something();
    }
}

CODE_SAMPLE;

    /**
     * @var string
     */
    public const EXAMPLE_CODE_AFTER = <<<'CODE_SAMPLE'
<?php

class SomeClass
{
    public function run()
    {
        $this->somethingElse();
    }
}

CODE_SAMPLE;

    /**
     * @var SymfonyStyle
     */
    private $symfonyStyle;

    /**
     * @var PackageNamesProvider
     */
    private $packageNamesProvider;

    /**
     * @var NodeTypesProvider
     */
    private $nodeTypesProvider;

    /**
     * @var SetsListProvider
     */
    private $setsListProvider;

    public function __construct(
        PackageNamesProvider $packageNamesProvider,
        NodeTypesProvider $nodeTypesProvider,
        SetsListProvider $setsListProvider,
        SymfonyStyle $symfonyStyle
    ) {
        $this->packageNamesProvider = $packageNamesProvider;
        $this->nodeTypesProvider = $nodeTypesProvider;
        $this->setsListProvider = $setsListProvider;
        $this->symfonyStyle = $symfonyStyle;
    }

    public function create(): RectorRecipe
    {
        $rectorRecipe = new RectorRecipe(
            $this->askForPackageName(),
            $this->askForRectorName(),
            $this->askForNodeTypes(),
            $this->askForRectorDescription(),
            self::EXAMPLE_CODE_BEFORE,
            self::EXAMPLE_CODE_AFTER,
        );

        $rectorRecipe->setResources($this->askForResources());

        $set = $this->askForSet();
        if ($set !== null) {
            $rectorRecipe->setSet($set);
        }

        return $rectorRecipe;
    }

    private function askForPackageName(): string
    {
        $question = new Question(sprintf(
            'Package name for which Rector should be created (e.g. <fg=yellow>%s</>)',
            'Naming'
        ));
        $question->setAutocompleterValues($this->packageNamesProvider->provide());

        $packageName = $this->symfonyStyle->askQuestion($question);

        return $packageName ?? $this->askForPackageName();
    }

    private function askForRectorName(): string
    {
        $question = sprintf(
            'Class name of the Rector to create (e.g. <fg=yellow>%s</>)',
            'RenameMethodCallRector',
        );
        $rectorName = $this->symfonyStyle->ask($question);

        return $rectorName ?? $this->askForRectorName();
    }

    /**
     * @return array<int, class-string>
     */
    private function askForNodeTypes(): array
    {
        $choiceQuestion = new ChoiceQuestion(sprintf(
            'For what Nodes should the Rector be run (e.g. <fg=yellow>%s</>)',
            'Expr/MethodCall',
        ), $this->nodeTypesProvider->provide());

        $choiceQuestion->setMultiselect(true);

        $nodeTypes = $this->symfonyStyle->askQuestion($choiceQuestion);

        $classes = [];
        foreach ($nodeTypes as $nodeType) {
            /** @var class-string $class */
            $class = 'PhpParser\Node\\' . $nodeType;
            $classes[] = $class;
        }

        return $classes;
    }

    private function askForRectorDescription(): string
    {
        $description = $this->symfonyStyle->ask('Short description of new Rector');

        return $description ?? $this->askForRectorDescription();
    }

    /**
     * @return array<string>
     */
    private function askForResources(): array
    {
        $resources = [];

        while (true) {
            $question = sprintf(
                'Link to resource that explains why the change is needed (e.g. <fg=yellow>%s</>)',
                'https://github.com/symfony/symfony/blob/704c648ba53be38ef2b0105c97c6497744fef8d8/UPGRADE-6.0.md',
            );
            $resource = $this->symfonyStyle->ask($question);

            if ($resource === null) {
                break;
            }

            $resources[] = $resource;
        }

        return $resources;
    }

    private function askForSet(): ?string
    {
        $question = new Question(sprintf('Set to which Rector should be added (e.g. <fg=yellow>%s</>)', 'SYMFONY_52'));
        $question->setAutocompleterValues($this->setsListProvider->provide());

        $setName = $this->symfonyStyle->askQuestion($question);
        if ($setName === null) {
            return null;
        }

        return constant(SetList::class . $setName);
    }
}
