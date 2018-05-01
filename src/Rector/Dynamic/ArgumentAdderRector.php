<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Rector\ArgumentAdderRecipe;
use Rector\Configuration\Rector\ArgumentAdderRecipeFactory;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ArgumentAdderRector extends AbstractArgumentRector
{
    /**
     * @var ArgumentAdderRecipe[]
     */
    private $argumentAdderRecipes = [];

    /**
     * @var ArgumentAdderRecipe[]
     */
    private $activeArgumentAdderRecipes = [];

    /**
     * @var ArgumentAdderRecipeFactory
     */
    private $argumentAdderRecipeFactory;

    /**
     * @param mixed[] $argumentChangesByMethodAndType
     */
    public function __construct(
        array $argumentChangesByMethodAndType,
        ArgumentAdderRecipeFactory $argumentAdderRecipeFactory
    ) {
        $this->argumentAdderRecipeFactory = $argumentAdderRecipeFactory;
        $this->loadArgumentReplacerRecipes($argumentChangesByMethodAndType);
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            '[Dynamic] Adds or replaces defined arguments in defined methods and their calls.',
            [
                new CodeSample(
                    '$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder;
$containerBuilder->compile();',
                    '$containerBuilder = new Symfony\Component\DependencyInjection\ContainerBuilder;
$containerBuilder->compile(true);'
                ),
            ]
        );
    }

    public function isCandidate(Node $node): bool
    {
        if (! $this->isValidInstance($node)) {
            return false;
        }

        $this->activeArgumentAdderRecipes = $this->matchArgumentChanges($node);

        return (bool) $this->activeArgumentAdderRecipes;
    }

    /**
     * @param MethodCall|StaticCall|ClassMethod $node
     */
    public function refactor(Node $node): Node
    {
        $argumentsOrParameters = $this->getNodeArgumentsOrParameters($node);
        $argumentsOrParameters = $this->processArgumentNodes($argumentsOrParameters);

        $this->setNodeArgumentsOrParameters($node, $argumentsOrParameters);

        return $node;
    }

    /**
     * @return ArgumentAdderRecipe[]
     */
    private function matchArgumentChanges(Node $node): array
    {
        $argumentReplacerRecipes = [];

        foreach ($this->argumentAdderRecipes as $argumentReplacerRecipe) {
            if ($this->isNodeToRecipeMatch($node, $argumentReplacerRecipe)) {
                $argumentReplacerRecipes[] = $argumentReplacerRecipe;
            }
        }

        return $argumentReplacerRecipes;
    }

    /**
     * @param mixed[] $configurationArrays
     */
    private function loadArgumentReplacerRecipes(array $configurationArrays): void
    {
        foreach ($configurationArrays as $configurationArray) {
            $this->argumentAdderRecipes[] = $this->argumentAdderRecipeFactory->createFromArray($configurationArray);
        }
    }

    /**
     * @param mixed[] $argumentNodes
     * @return mixed[]
     */
    private function processArgumentNodes(array $argumentNodes): array
    {
        foreach ($this->activeArgumentAdderRecipes as $argumentReplacerRecipe) {
            $position = $argumentReplacerRecipe->getPosition();

            $argumentNodes[$position] = BuilderHelpers::normalizeValue($argumentReplacerRecipe->getReplacement());
        }

        return $argumentNodes;
    }
}
