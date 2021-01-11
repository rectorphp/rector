<?php

declare(strict_types=1);

namespace Rector\SymfonyCodeQuality\Rector\Attribute;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\ClassConstFetch;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\Util\StaticRectorStrings;
use Rector\SymfonyCodeQuality\NodeFactory\RouteNameClassFactory;
use Rector\SymfonyCodeQuality\ValueObject\ClassName;
use Symfony\Component\Routing\Annotation\Route;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see https://tomasvotruba.com/blog/2020/12/21/5-new-combos-opened-by-symfony-52-and-php-80/
 *
 * @see \Rector\SymfonyCodeQuality\Tests\Rector\Attribute\ExtractAttributeRouteNameConstantsRector\ExtractAttributeRouteNameConstantsRectorTest
 */
final class ExtractAttributeRouteNameConstantsRector extends AbstractRector
{
    /**
     * @var RouteNameClassFactory
     */
    private $routeNameClassFactory;

    public function __construct(RouteNameClassFactory $routeNameClassFactory)
    {
        $this->routeNameClassFactory = $routeNameClassFactory;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Extract #[Route] attribute name argument from string to constant', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

class SomeClass
{
    #[Route(path: "path", name: "/name")]
    public function run()
    {
    }
}
CODE_SAMPLE

                ,
                <<<'CODE_SAMPLE'
use Symfony\Component\Routing\Annotation\Route;

class SomeClass
{
    #[Route(path: "path", name: RouteName::NAME)]
    public function run()
    {
    }
}
CODE_SAMPLE

            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Attribute::class];
    }

    /**
     * @param Attribute $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isName($node->name, Route::class)) {
            return null;
        }

        $collectedConstantsToValues = [];

        foreach ($node->args as $arg) {
            if (! $this->isName($arg, 'name')) {
                continue;
            }

            if ($arg->value instanceof ClassConstFetch) {
                continue;
            }

            $argumentValue = $this->getValue($arg->value);
            if (! is_string($argumentValue)) {
                continue;
            }

            $constantName = StaticRectorStrings::camelCaseToConstant($argumentValue);
            $arg->value = $this->createClassConstFetch(ClassName::ROUTE_CLASS_NAME, $constantName);

            $collectedConstantsToValues[$constantName] = $argumentValue;
        }

        if ($collectedConstantsToValues === []) {
            return null;
        }

        $namespace = $this->routeNameClassFactory->create($collectedConstantsToValues);
        $this->printNodesToFilePath([$namespace], 'src/ValueObject/Routing/RouteName.php');

        return $node;
    }
}
