<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Utils;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterReflection\Reflector\SmartClassReflector;
use Rector\Builder\MethodBuilder;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Rector\AbstractRector;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;
use Rector\Regex\MagicMethodMatcher;

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
     * @var SmartClassReflector
     */
    private $smartClassReflector;

    /**
     * @var MagicMethodMatcher
     */
    private $magicMethodMatcher;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    public function __construct(
        MethodBuilder $methodBuilder,
        DocBlockAnalyzer $docBlockAnalyzer,
        SmartClassReflector $smartClassReflector,
        MagicMethodMatcher $magicMethodMatcher,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->methodBuilder = $methodBuilder;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->smartClassReflector = $smartClassReflector;
        $this->magicMethodMatcher = $magicMethodMatcher;
        $this->nodeTypeResolver = $nodeTypeResolver;
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

        $className = $node->getAttribute(Attribute::CLASS_NAME);

        $classReflection = $this->smartClassReflector->reflect($className);

        if ($classReflection === null) {
            return false;
        }

        $this->magicMethods = $this->magicMethodMatcher->matchInContent(
            $classReflection,
            $docComments[0]->getText()
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
                $methodSettings['operation'],
                $methodSettings['argumentName']
            );

            $this->docBlockAnalyzer->removeAnnotationFromNode($classNode, 'method', $methodName);
        }

        return $classNode;
    }

    private function isNetteObjectChild(Class_ $classNode): bool
    {
        $classNodeTypes = $this->nodeTypeResolver->resolve($classNode);

        return in_array('Nette\Object', $classNodeTypes, true);
    }
}
