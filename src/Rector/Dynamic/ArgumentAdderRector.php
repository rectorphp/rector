<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\BuilderHelpers;
use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Rector\ArgumentAdderRecipe;
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
     * @param mixed[] $argumentChangesByMethodAndType
     */
    public function __construct(array $argumentChangesByMethodAndType)
    {
        foreach ($argumentChangesByMethodAndType as $configurationArray) {
            $this->argumentAdderRecipes[] = ArgumentAdderRecipe::createFromArray($configurationArray);
        }
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'This Rector adds new default arguments in calls of defined methods and class types.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod();
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod(true);
CODE_SAMPLE
                ),
                new CodeSample(
                    <<<'CODE_SAMPLE'
class MyCustomClass extends SomeClass
{
    public function someMethod()
    {
    }
}
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
class MyCustomClass extends SomeClass
{
    public function someMethod($value = true)
    {
    }
}
CODE_SAMPLE
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
     * @param mixed[] $argumentNodes
     * @return mixed[]
     */
    private function processArgumentNodes(array $argumentNodes): array
    {
        foreach ($this->activeArgumentAdderRecipes as $argumentReplacerRecipe) {
            $position = $argumentReplacerRecipe->getPosition();

            $argumentNodes[$position] = BuilderHelpers::normalizeValue($argumentReplacerRecipe->getDefaultValue());
        }

        return $argumentNodes;
    }
}
