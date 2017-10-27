<?php
declare(strict_types=1);

namespace Roave\BetterReflection\SourceLocator\Ast;

use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\NodeVisitorAbstract;
use Roave\BetterReflection\Identifier\IdentifierType;
use Roave\BetterReflection\Reflector\Reflector;
use Roave\BetterReflection\SourceLocator\Ast\Strategy\AstConversionStrategy;
use Roave\BetterReflection\SourceLocator\Located\LocatedSource;

/**
 * @internal
 */
final class FindReflectionsInTree
{
    /**
     * @var AstConversionStrategy
     */
    private $astConversionStrategy;

    public function __construct(AstConversionStrategy $astConversionStrategy)
    {
        $this->astConversionStrategy = $astConversionStrategy;
    }

    /**
     * Find all reflections of a given type in an Abstract Syntax Tree
     *
     * @param Reflector      $reflector
     * @param Node[]         $ast
     * @param IdentifierType $identifierType
     * @param LocatedSource  $locatedSource
     *
     * @return \Roave\BetterReflection\Reflection\Reflection[]
     */
    public function __invoke(
        Reflector $reflector,
        array $ast,
        IdentifierType $identifierType,
        LocatedSource $locatedSource
    ) : array {
        $nodeVisitor = new class($reflector, $identifierType, $locatedSource, $this->astConversionStrategy)
            extends NodeVisitorAbstract
        {
            /**
             * @var \Roave\BetterReflection\Reflection\Reflection[]
             */
            private $reflections = [];

            /**
             * @var Reflector
             */
            private $reflector;

            /**
             * @var IdentifierType
             */
            private $identifierType;

            /**
             * @var LocatedSource
             */
            private $locatedSource;

            /**
             * @var AstConversionStrategy
             */
            private $astConversionStrategy;

            /**
             * @var Namespace_|null
             */
            private $currentNamespace;

            public function __construct(
                Reflector $reflector,
                IdentifierType $identifierType,
                LocatedSource $locatedSource,
                AstConversionStrategy $astConversionStrategy
            ) {
                $this->reflector             = $reflector;
                $this->identifierType        = $identifierType;
                $this->locatedSource         = $locatedSource;
                $this->astConversionStrategy = $astConversionStrategy;
            }

            public function enterNode(Node $node) : void
            {
                if ($node instanceof Namespace_) {
                    $this->currentNamespace = $node;

                    return;
                }

                if ($node instanceof Node\Stmt\ClassLike) {
                    $classNamespace = null === $node->name ? null : $this->currentNamespace;
                    $reflection     = $this->astConversionStrategy->__invoke($this->reflector, $node, $this->locatedSource, $classNamespace);

                    if ($this->identifierType->isMatchingReflector($reflection)) {
                        $this->reflections[] = $reflection;
                    }

                    return;
                }

                if ($node instanceof Node\Stmt\Function_) {
                    $reflection = $this->astConversionStrategy->__invoke($this->reflector, $node, $this->locatedSource, $this->currentNamespace);

                    if ($this->identifierType->isMatchingReflector($reflection)) {
                        $this->reflections[] = $reflection;
                    }
                }
            }

            public function leaveNode(Node $node) : void
            {
                if ($node instanceof Namespace_) {
                    $this->currentNamespace = null;
                }
            }

            /**
             * @return \Roave\BetterReflection\Reflection\Reflection[]
             */
            public function getReflections() : array
            {
                return $this->reflections;
            }
        };

        $nodeTraverser = new NodeTraverser();
        $nodeTraverser->addVisitor(new NameResolver());
        $nodeTraverser->addVisitor($nodeVisitor);
        $nodeTraverser->traverse($ast);

        return $nodeVisitor->getReflections();
    }
}
