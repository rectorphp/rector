<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\Configuration\Rector\ArgumentRemoverRecipe;
use Rector\Node\Attribute;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;

final class ArgumentRemoverRector extends AbstractArgumentRector
{
    /**
     * @var ArgumentRemoverRecipe[]
     */
    private $argumentRemoverRecipes = [];

    /**
     * @var ArgumentRemoverRecipe[]
     */
    private $activeArgumentRemoverRecipes = [];

    /**
     * @param mixed[] $argumentChangesByMethodAndType
     */
    public function __construct(array $argumentChangesByMethodAndType)
    {
        foreach ($argumentChangesByMethodAndType as $configurationArray) {
            $this->argumentRemoverRecipes[] = ArgumentRemoverRecipe::createFromArray($configurationArray);
        }
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            '[Dynamic] Removes defined arguments in defined methods and their calls.',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod(true);
CODE_SAMPLE
                    ,
                    <<<'CODE_SAMPLE'
$someObject = new SomeClass;
$someObject->someMethod();'
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

        $this->activeArgumentRemoverRecipes = $this->matchArgumentChanges($node);

        return (bool) $this->activeArgumentRemoverRecipes;
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
     * @return ArgumentRemoverRecipe[]
     */
    private function matchArgumentChanges(Node $node): array
    {
        $argumentReplacerRecipes = [];

        foreach ($this->argumentRemoverRecipes as $argumentRemoverRecipe) {
            if ($this->isNodeToRecipeMatch($node, $argumentRemoverRecipe)) {
                $argumentReplacerRecipes[] = $argumentRemoverRecipe;
            }
        }

        return $argumentReplacerRecipes;
    }

    /**
     * @param Arg[]|Param[] $argumentNodes
     * @return mixed[]
     */
    private function processArgumentNodes(array $argumentNodes): array
    {
        foreach ($this->activeArgumentRemoverRecipes as $activeArgumentRemoverRecipe) {
            $position = $activeArgumentRemoverRecipe->getPosition();

            if ($activeArgumentRemoverRecipe->getValue() === null) {
                unset($argumentNodes[$position]);
            } elseif ($this->isArgumentValueMatch($argumentNodes[$position], $activeArgumentRemoverRecipe)) {
                unset($argumentNodes[$position]);
            }
        }

        return $argumentNodes;
    }

    /**
     * @param Arg|Param $argOrParamNode
     */
    private function isArgumentValueMatch($argOrParamNode, ArgumentRemoverRecipe $argumentRemoverRecipe): bool
    {
        // only argument specific value can be removed
        if (! $argOrParamNode instanceof Arg) {
            return false;
        }

        $valueNode = $argOrParamNode->value;

        if ($valueNode instanceof ClassConstFetch) {
            $valueNodeAsString = $valueNode->class->getAttribute(Attribute::RESOLVED_NAME)->toString()
                . '::'
                . $valueNode->name->toString();

            if ($valueNodeAsString === $argumentRemoverRecipe->getValue()) {
                return true;
            }
        }

        return false;
    }
}
