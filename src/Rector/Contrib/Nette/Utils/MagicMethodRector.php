<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette\Utils;

use PhpParser\Comment\Doc;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\BetterPhpDocParser\NodeAnalyzer\DocBlockAnalyzer;
use Rector\BetterReflection\Reflector\SmartClassReflector;
use Rector\Builder\MethodBuilder;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\Regex\MagicMethodMatcher;

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

    /**
     * @var string
     */
    private $parentClass;

    public function __construct(
        MethodBuilder $methodBuilder,
        DocBlockAnalyzer $docBlockAnalyzer,
        SmartClassReflector $smartClassReflector,
        MagicMethodMatcher $magicMethodMatcher,
        NodeTypeResolver $nodeTypeResolver,
        string $parentClass = 'Nette\Object'
    ) {
        $this->methodBuilder = $methodBuilder;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->smartClassReflector = $smartClassReflector;
        $this->magicMethodMatcher = $magicMethodMatcher;
        $this->nodeTypeResolver = $nodeTypeResolver;
        $this->parentClass = $parentClass;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Catches @method annotations of Nette\Object instances and converts them to real methods.',
            [new CodeSample(
                <<<'CODE_SAMPLE'
/**
 * @method getId()
 */
class extends Nette\Object
{
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
class extends Nette\Object
{
    public function getId()
    {
        return $this->id;
    }
}
CODE_SAMPLE
            )]
        );
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

        /** @var Doc[]|null $docComments */
        $docComments = $node->getAttribute('comments');
        if ($docComments === null) {
            return false;
        }

        /** @var string $className */
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

            $this->docBlockAnalyzer->removeTagWithContentFromNode($classNode, 'method', $methodName);
        }

        return $classNode;
    }

    private function isNetteObjectChild(Class_ $classNode): bool
    {
        $classNodeTypes = $this->nodeTypeResolver->resolve($classNode);

        return in_array($this->parentClass, $classNodeTypes, true);
    }
}
