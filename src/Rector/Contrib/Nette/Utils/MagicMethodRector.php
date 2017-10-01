<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Utils;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterReflection\Reflector\CurrentFileAwareClassReflector;
use Rector\Builder\MethodBuilder;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Rector\AbstractRector;
use Rector\Regex\MagicMethodMatcher;
use Roave\BetterReflection\Reflection\ReflectionClass;

/**
 * Catches @method annotations at childs of Nette\Object
 * and converts them to real methods
 */
final class MagicMethodRector extends AbstractRector
{
    /**
     * @var mixed[]
     */
    private $magicMethods = [];

    /**
     * @var MethodBuilder
     */
    private $methodBuilder;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var ReflectionClass
     */
    private $classReflection;

    /**
     * @var CurrentFileAwareClassReflector
     */
    private $currentFileAwareClassReflector;

    /**
     * @var MagicMethodMatcher
     */
    private $magicMethodMatcher;

    public function __construct(
        MethodBuilder $methodBuilder,
        DocBlockAnalyzer $docBlockAnalyzer,
        CurrentFileAwareClassReflector $currentFileAwareClassReflector,
        MagicMethodMatcher $magicMethodMatcher
    ) {
        $this->methodBuilder = $methodBuilder;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->currentFileAwareClassReflector = $currentFileAwareClassReflector;
        $this->magicMethodMatcher = $magicMethodMatcher;
    }

    public function isCandidate(Node $node): bool
    {
        $this->magicMethods = [];

        if (! $node instanceof Class_) {
            return false;
        }

        if (! $this->isNetteObjectChild($node)) {
            return false;
        }

        $docComments = $node->getAttribute('comments');
        if ($docComments === null) {
            return false;
        }

        /** @var string $className */
        $className = $node->getAttribute(Attribute::CLASS_NAME);
        $this->classReflection = $this->currentFileAwareClassReflector->reflect($className);

        /** @var Doc $docComment */
        $docComment = $docComments[0];

        // @todo consider NamespaceResolver NodeTraverser
        $currentNamespace = $node->namespacedName->slice(0, -1)
            ->toString();

        $this->magicMethods = $this->magicMethodMatcher->matchInContent(
            $this->classReflection,
            $currentNamespace,
            $docComment->getText()
        );

        return (bool) count($this->magicMethods);
    }

    /**
     * @param Class_ $classNode
     */
    public function refactor(Node $classNode): ?Node
    {
        // reverse methods, to add them from the top
        $this->magicMethods = array_reverse($this->magicMethods, true);

        foreach ($this->magicMethods as $methodName => $methodSettings) {
            $this->methodBuilder->addMethodToClass(
                $classNode,
                $methodName,
                $methodSettings['propertyType'],
                $methodSettings['propertyName'],
                $methodSettings['operation']
            );

            $this->docBlockAnalyzer->removeAnnotationFromNode($classNode, 'method', $methodName);
        }

        return $classNode;
    }

    private function isNetteObjectChild(Class_ $classNode): bool
    {
        if ($classNode->extends === null) {
            return false;
        }

        $parentClassName = (string) $classNode->extends->getAttribute(Attribute::RESOLVED_NAME);

        return $parentClassName === 'Nette\Object';
    }
}
