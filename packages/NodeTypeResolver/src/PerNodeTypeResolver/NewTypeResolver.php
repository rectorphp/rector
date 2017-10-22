<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\PerNodeTypeResolver;

use PhpParser\Node;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name\FullyQualified;
use Rector\Exception\NotImplementedException;
use Rector\Node\Attribute;
use Rector\NodeAnalyzer\ClassAnalyzer;
use Rector\NodeTypeResolver\Contract\PerNodeTypeResolver\PerNodeTypeResolverInterface;
use Rector\NodeTypeResolver\TypeContext;

final class NewTypeResolver implements PerNodeTypeResolverInterface
{
    /**
     * @var TypeContext
     */
    private $typeContext;

    /**
     * @var ClassAnalyzer
     */
    private $classAnalyzer;

    public function __construct(TypeContext $typeContext, ClassAnalyzer $classAnalyzer)
    {
        $this->typeContext = $typeContext;
        $this->classAnalyzer = $classAnalyzer;
    }

    public function getNodeClass(): string
    {
        return New_::class;
    }

    /**
     * @param New_ $newNode
     */
    public function resolve(Node $newNode): ?string
    {
        // e.g. new class extends AnotherClass();

        if ($this->classAnalyzer->isAnonymousClassNode($newNode->class)) {
            $classNode = $newNode->class;
            if (! $classNode->extends instanceof Node\Name) {
                return null;
            }

            // @todo: add interfaces as well
            // use some gettypesForClass($class) method, already used somewhere else

            $resolvedName = $classNode->extends->getAttribute(Attribute::RESOLVED_NAME);
            if ($resolvedName instanceof FullyQualified) {
                return $resolvedName->toString();
            }

            return null;
        }

        // e.g. new $someClass;
        if ($newNode->class instanceof Variable) {
            // can be anything (dynamic)
            $variableName = $newNode->class->name;

            return $this->typeContext->getTypeForVariable($variableName);
        }

        // e.g. new SomeClass;
        if ($newNode->class instanceof Node\Name) {
            /** @var FullyQualified $fqnName */
            $fqnName = $newNode->class->getAttribute(Attribute::RESOLVED_NAME);

            return $fqnName->toString();
        }

        // e.g. new $this->templateClass;
        if ($newNode->class instanceof PropertyFetch) {
            if ($newNode->class->var->name !== 'this') {
                throw new NotImplementedException(sprintf(
                    'Not implemented yet. Go to "%s()" and add check for "%s" node for external dependency.',
                    __METHOD__,
                    get_class($newNode->class)
                ));
            }

            // can be anything (dynamic)
            $propertyName = (string) $newNode->class->name;

            return $this->typeContext->getTypeForProperty($propertyName);
        }

        throw new NotImplementedException(sprintf(
            'Not implemented yet. Go to "%s()" and add check for "%s" node.',
            __METHOD__,
            get_class($newNode->class)
        ));
    }
}
