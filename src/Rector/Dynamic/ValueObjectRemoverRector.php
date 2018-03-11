<?php declare(strict_types=1);

namespace Rector\Rector\Dynamic;

use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Property;
use Rector\Node\Attribute;
use Rector\NodeTypeResolver\NodeTypeResolver;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\ReflectionDocBlock\NodeAnalyzer\DocBlockAnalyzer;

final class ValueObjectRemoverRector extends AbstractRector
{
    /**
     * @var string[]
     */
    private $valueObjectsToSimpleTypes = [];

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var NodeTypeResolver
     */
    private $nodeTypeResolver;

    /**
     * @param string[] $valueObjectsToSimpleTypes
     */
    public function __construct(
        array $valueObjectsToSimpleTypes,
        DocBlockAnalyzer $docBlockAnalyzer,
        NodeTypeResolver $nodeTypeResolver
    ) {
        $this->valueObjectsToSimpleTypes = $valueObjectsToSimpleTypes;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->nodeTypeResolver = $nodeTypeResolver;
    }

    /**
     * @todo complete the rest of cases
     */
    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('[Dynamic] Remove values objects and use directly the value.', [
            new CodeSample('$someValue = new SomeValueObject("just_a_string");', '$someValue = "just_a_string";'),
        ]);
    }

    public function isCandidate(Node $node): bool
    {
        if ($node instanceof New_) {
            return $this->processNewCandidate($node);
        }

        if ($node instanceof Property) {
            return $this->processPropertyCandidate($node);
        }

        if ($node instanceof Name) {
            $parentNode = $node->getAttribute(Attribute::PARENT_NODE);
            if ($parentNode instanceof Param) {
                return true;
            }
        }

        if ($node instanceof NullableType) {
            return true;
        }

        // for docs update
        if ($node instanceof Variable) {
            return true;
        }

        return false;
    }

    /**
     * @param New_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($node instanceof New_) {
            return $node->args[0];
        }

        if ($node instanceof Property) {
            return $this->refactorProperty($node);
        }

        if ($node instanceof Name) {
            $newType = $this->matchNewType($node);
            if ($newType === null) {
                return null;
            }

            return new Name($newType);
        }

        if ($node instanceof NullableType) {
            $newType = $this->matchNewType($node->type);
            if (! $newType) {
                return $node;
            }

            $parentNode = $node->getAttribute(Attribute::PARENT_NODE);

            // in method parameter update docs as well
            if ($parentNode instanceof Param) {
                /** @var ClassMethod $classMethodNode */
                $classMethodNode = $parentNode->getAttribute(Attribute::PARENT_NODE);

                $this->renameNullableInDocBlock($classMethodNode, (string) $node->type, $newType);
            }

            return new NullableType($newType);
        }

        if ($node instanceof Variable) {
            $match = $this->matchOriginAndNewType($node);
            if (! $match) {
                return $node;
            }

            [$oldType, $newType] = $match;

            $this->renameNullableInDocBlock($node, $oldType, $newType);

            // @todo use right away?
            // SingleName - no slashes or partial uses => return
            if (! Strings::contains($oldType, '\\')) {
                return $node;
            }

            // SomeNamespace\SomeName - possibly used only part in docs blocks
            $oldTypeParts = explode('\\', $oldType);
            $oldTypeParts = array_reverse($oldTypeParts);

            $oldType = '';
            foreach ($oldTypeParts as $oldTypePart) {
                $oldType .= $oldTypePart;

                $this->renameNullableInDocBlock($node, $oldType, $newType);
                $oldType .= '\\';
            }

            dump($node->getDocComment());

            return $node;
        }


        return null;
    }

    private function processNewCandidate(New_ $newNode): bool
    {
        if (count($newNode->args) !== 1) {
            return false;
        }

        $classNodeTypes = $this->nodeTypeResolver->resolve($newNode->class);

        return (bool) array_intersect($classNodeTypes, $this->getValueObjects());
    }

    /**
     * @return string[]
     */
    private function getValueObjects(): array
    {
        return array_keys($this->valueObjectsToSimpleTypes);
    }

    private function processPropertyCandidate(Property $propertyNode): bool
    {
        $propertyNodeTypes = $this->nodeTypeResolver->resolve($propertyNode);

        return (bool) array_intersect($propertyNodeTypes, $this->getValueObjects());
    }

    private function refactorProperty(Property $propertyNode): Property
    {
        $newType = $this->matchNewType($propertyNode);
        if ($newType === null) {
            return $propertyNode;
        }

        $this->docBlockAnalyzer->replaceVarType($propertyNode, $newType);

        return $propertyNode;
    }

    private function matchNewType(Node $node): ?string
    {
        $nodeTypes = $this->nodeTypeResolver->resolve($node);
        foreach ($nodeTypes as $nodeType) {
            if (! isset($this->valueObjectsToSimpleTypes[$nodeType])) {
                continue;
            }

            dump($nodeType);
            return $this->valueObjectsToSimpleTypes[$nodeType];
        }

        return null;
    }

    private function matchOriginAndNewType(Node $node): ?array
    {
        $nodeTypes = $this->nodeTypeResolver->resolve($node);
        foreach ($nodeTypes as $nodeType) {
            if (! isset($this->valueObjectsToSimpleTypes[$nodeType])) {
                continue;
            }

            return [
                $nodeType,
                $this->valueObjectsToSimpleTypes[$nodeType]
            ];
        }

        return null;
    }

    private function renameNullableInDocBlock(Node $node, string $oldType, string $newType): void
    {
        $this->docBlockAnalyzer->replaceInNode($node,
            sprintf('%s|null', $oldType),
            sprintf('%s|null', $newType)
        );

        $this->docBlockAnalyzer->replaceInNode(
            $node,
            sprintf('null|%s', $oldType),
            sprintf('null|%s', $newType)
        );
    }
}
