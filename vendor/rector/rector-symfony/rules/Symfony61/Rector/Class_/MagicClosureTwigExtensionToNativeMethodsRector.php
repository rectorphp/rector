<?php

declare (strict_types=1);
namespace Rector\Symfony\Symfony61\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\VariadicPlaceholder;
use PHPStan\Analyser\Scope;
use PHPStan\Type\ObjectType;
use Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher;
use Rector\NodeCollector\ValueObject\ArrayCallable;
use Rector\Rector\AbstractScopeAwareRector;
use Rector\ValueObject\PhpVersion;
use Rector\VersionBonding\Contract\MinPhpVersionInterface;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Symfony\Tests\Symfony61\Rector\Class_\MagicClosureTwigExtensionToNativeMethodsRector\MagicClosureTwigExtensionToNativeMethodsRectorTest
 *
 * @see PHP 8.1 way to handle functions/filters https://github.com/symfony/symfony/blob/e0ad2eead3513a558c09d8aa3ae9e867fb10b419/src/Symfony/Bridge/Twig/Extension/CodeExtension.php#L41-L52
 */
final class MagicClosureTwigExtensionToNativeMethodsRector extends AbstractScopeAwareRector implements MinPhpVersionInterface
{
    /**
     * @readonly
     * @var \Rector\NodeCollector\NodeAnalyzer\ArrayCallableMethodMatcher
     */
    private $arrayCallableMethodMatcher;
    public function __construct(ArrayCallableMethodMatcher $arrayCallableMethodMatcher)
    {
        $this->arrayCallableMethodMatcher = $arrayCallableMethodMatcher;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change TwigExtension function/filter magic closures to inlined and clear callables', [new CodeSample(<<<'CODE_SAMPLE'
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TerminologyExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('resolve', [$this, 'resolve']);
        ];
    }

    private function resolve($value)
    {
        return $value + 100;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class TerminologyExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction('resolve', $this->resolve(...)),
        ];
    }

    private function resolve($value)
    {
        return $value + 100;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactorWithScope(Node $node, Scope $scope) : ?Node
    {
        if (!$this->nodeTypeResolver->isObjectTypes($node, [new ObjectType('Twig_ExtensionInterface'), new ObjectType('Twig\\Extension\\ExtensionInterface')])) {
            return null;
        }
        $hasFunctionsChanged = \false;
        $getFunctionsClassMethod = $node->getMethod('getFunctions');
        if ($getFunctionsClassMethod instanceof ClassMethod) {
            $hasFunctionsChanged = $this->refactorClassMethod($getFunctionsClassMethod, $scope);
        }
        $hasFiltersChanged = \false;
        $getFiltersClassMethod = $node->getMethod('getFilters');
        if ($getFiltersClassMethod instanceof ClassMethod) {
            $hasFiltersChanged = $this->refactorClassMethod($getFiltersClassMethod, $scope);
        }
        if ($hasFiltersChanged || $hasFunctionsChanged) {
            return $node;
        }
        return null;
    }
    public function provideMinPhpVersion() : int
    {
        return PhpVersion::PHP_81;
    }
    private function refactorClassMethod(ClassMethod $classMethod, Scope $scope) : bool
    {
        $hasChanged = \false;
        $this->traverseNodesWithCallable($classMethod, function (Node $node) use(&$hasChanged, $scope) : ?Node {
            if (!$node instanceof Array_) {
                return null;
            }
            $arrayCallable = $this->arrayCallableMethodMatcher->match($node, $scope);
            if (!$arrayCallable instanceof ArrayCallable) {
                return null;
            }
            $hasChanged = \true;
            return new MethodCall($arrayCallable->getCallerExpr(), $arrayCallable->getMethod(), [new VariadicPlaceholder()]);
        });
        return $hasChanged;
    }
}
