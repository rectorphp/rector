<?php declare(strict_types=1);

/**
 * This class was used to automate huge refactoring in https://github.com/rectorphp/rector/pull/584
 *
 * It helped with 7-step refactoring of 96 files:
 * - 1. replace "isCandidate()" method by "getNodeType()" method
 * - 2. rename "return false;" to "return null;" to respect "refactor(Node $node): ?Node" typehint
 * - 3. rename used variable "$node" to "$specificTypeParam"
 * - 4. turn last return in "isCondition()" with early return
 * - 5. return true makes no sense anymore, just continue
 * - 6. remove first "instanceof", already covered by getNodeType()
 * - 7. add contents of "isCandidate()" method to start of "refactor()" method
 *
 * It took ~2 hours to setup. Saved work of 5-6 hours and much more stress :)
 */

namespace Rector\Examples;

use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\ConstFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Nop;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\If_;
use PHPStan\PhpDocParser\Ast\PhpDoc\ParamTagValueNode;
use PHPStan\PhpDocParser\Ast\Type\IdentifierTypeNode;
use PHPStan\PhpDocParser\Ast\Type\UnionTypeNode;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockAnalyzer;
use Rector\NodeTypeResolver\PHPStan\Type\TypeToStringResolver;
use Rector\Rector\AbstractRector;
use Rector\RectorDefinition\CodeSample;
use Rector\RectorDefinition\RectorDefinition;
use Rector\PhpParser\NodeTraverser\CallableNodeTraverser;

final class MergeIsCandidateRector extends AbstractRector
{
    /**
     * @var BuilderFactory
     */
    private $builderFactory;

    /**
     * @var DocBlockAnalyzer
     */
    private $docBlockAnalyzer;

    /**
     * @var TypeToStringResolver
     */
    private $typeToStringResolver;

    /**
     * @var CallableNodeTraverser
     */
    private $callbackNodeTraverser;

    public function __construct(
        BuilderFactory $builderFactory,
        DocBlockAnalyzer $docBlockAnalyzer,
        TypeToStringResolver $typeToStringResolver,
        CallableNodeTraverser $callbackNodeTraverser
    ) {
        $this->builderFactory = $builderFactory;
        $this->docBlockAnalyzer = $docBlockAnalyzer;
        $this->typeToStringResolver = $typeToStringResolver;
        $this->callbackNodeTraverser = $callbackNodeTraverser;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition(
            'Finds all "Rector\Rector\AbstractRector" instances, merges "isCandidate()" method to "refactor()" method and creates "getNodeType()" method by @param annotation of "refactor()" method.',
            [new CodeSample('', '')]
        );
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }

    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isType($node, 'Rector\Rector\AbstractRector')) {
            return null;
        }

        if (! $node->isAbstract()) {
            return null;
        }

        // has "isCandidate()" method?
        if (! $this->hasClassIsCandidateMethod($node)) {
            return null;
        }

        [$isCandidateClassMethodPosition, $isCandidateClassMethod] = $this->getClassMethodByName($node, 'isCandidate');
        [$refactorClassMethodPosition, $refactorClassMethod] = $this->getClassMethodByName($node, 'refactor');

        if ($refactorClassMethod === null) {
            return null;
        }

        // 1. replace "isCandidate()" method by "getNodeType()" method
        $node->stmts[$isCandidateClassMethodPosition] = $this->createGetNodeTypeClassMethod($refactorClassMethod);

        // 2. rename "return false;" to "return null;" to respect "refactor(Node $node): ?Node" typehint
        $this->replaceReturnFalseWithReturnNull($isCandidateClassMethod);

        // 3. rename used variable "$node" to "$specificTypeParam"
        $this->renameNodeToParamNode($isCandidateClassMethod, $refactorClassMethod->params[0]->var->name);

        // 4. turn last return in "isCondition()" with early return
        $this->replaceLastReturnWithIf($isCandidateClassMethod);

        // 5. return true makes no sense anymore, just continue
        $this->removeReturnTrue($isCandidateClassMethod);

        // 6. remove first "instanceof", already covered by getNodeType()
        $isCandidateClassMethod = $this->removeFirstInstanceOf($isCandidateClassMethod);

        // 7. add contents of "isCandidate()" method to start of "refactor()" method
        $refactorClassMethod->stmts = array_merge($isCandidateClassMethod->stmts, $refactorClassMethod->stmts);

        $node->stmts[$refactorClassMethodPosition] = $refactorClassMethod;

        return $node;
    }

    private function hasClassIsCandidateMethod(Class_ $classNode): bool
    {
        return (bool) $this->getClassMethodByName($classNode, 'isCandidate');
    }

    /**
     * @return int[]|ClassMethod[]|null
     */
    private function getClassMethodByName(Class_ $classNode, string $name)
    {
        foreach ($classNode->stmts as $i => $stmt) {
            if (! $stmt instanceof ClassMethod) {
                continue;
            }

            if ($this->isName($stmt->name, $name)) {
                return [$i, $stmt];
            }
        }

        return null;
    }

    /**
     * @return string[]
     */
    private function resolveParamTagValueNodeToStrings(ParamTagValueNode $paramTagValueNode): array
    {
        $types = [];

        if ($paramTagValueNode->type instanceof UnionTypeNode) {
            foreach ($paramTagValueNode->type->types as $type) {
                $types[] = (string) $type;
            }
        } elseif ($paramTagValueNode->type instanceof IdentifierTypeNode) {
            $types[] = $paramTagValueNode->type->name;
        } else {
            dump($paramTagValueNode->type);
            // todo: resolve
        }

        return $types;
    }

    private function createGetNodeTypeClassMethod(ClassMethod $refactorClassMethod): ClassMethod
    {
        $paramTypes = $this->resolveSingleParamTypesFromClassMethod($refactorClassMethod);

        $nodeToBeReturned = new Array_();

        if (count($paramTypes) > 1) {
            foreach ($paramTypes as $paramType) {
                $classConstFetchNode = $this->createClassConstFetchFromClassName($paramType);
                $nodeToBeReturned->items[] = new ArrayItem($classConstFetchNode);
            }

        } elseif (count($paramTypes) === 1) {
            $nodeToBeReturned->items[] = $this->createClassConstFetchFromClassName($paramTypes[0]);
        } else { // fallback to basic node
            $nodeToBeReturned->items[] = $this->createClassConstFetchFromClassName('PhpParser\\Node');
        }

        return $this->builderFactory->method('getNodeTypes')
            ->makePublic()
            ->setReturnType('array')
            ->addStmt(new Return_($nodeToBeReturned))
            ->getNode();
    }

    /**
     * @return string[]
     */
    private function resolveSingleParamTypesFromClassMethod(ClassMethod $classMethod): array
    {
        // add getNodeType() by $refactorClassMethod "@param" doc type
        if (! $this->docBlockAnalyzer->hasTag($classMethod, 'param')) {
            return [];
        }

        $paramNode = $this->docBlockAnalyzer->getTagByName($classMethod, 'param');

        /** @var ParamTagValueNode $paramTagValueNode */
        $paramTagValueNode = $paramNode->value;

        return $this->resolveParamTagValueNodeToStrings($paramTagValueNode);
    }

    private function createClassConstFetchFromClassName(string $className): ClassConstFetch
    {
        return new ClassConstFetch(new FullyQualified($className), 'class');
    }

    private function replaceReturnFalseWithReturnNull(ClassMethod $classMethod): void
    {
        $this->callbackNodeTraverser->traverseNodesWithCallable([$classMethod], function (Node $node): ?Node {
            if (! $node instanceof Return_ || ! $node->expr instanceof ConstFetch) {
                return null;
            }

            if ($this->isFalse($node->expr)) {
                return new Return_(new ConstFetch(new Name('null')));
            }

            return null;
        });
    }

    private function renameNodeToParamNode(ClassMethod $classMethod, string $nodeName): void
    {
        $this->callbackNodeTraverser->traverseNodesWithCallable([$classMethod], function (Node $node) use ($nodeName): ?Node {
            if (! $node instanceof Variable || ! $this->isName($node, 'node')) {
                return null;
            }

            $node->name = $nodeName;

            return $node;
        });
    }

    private function replaceLastReturnWithIf(ClassMethod $classMethod): void
    {
        $this->callbackNodeTraverser->traverseNodesWithCallable([$classMethod], function (Node $node): ?Node {
            if (! $node instanceof Return_) {
                return null;
            }

            if ($node->expr instanceof ConstFetch) {
                return null;
            }

            $identicalCondition = new Identical($node->expr,  new ConstFetch(new Name('false')));
            return new If_($identicalCondition, [
                'stmts' => [
                    new Return_(new ConstFetch(new Name('null')))
                ]
            ]);
        });
    }

    private function removeReturnTrue(ClassMethod $classMethod): void
    {
        $this->callbackNodeTraverser->traverseNodesWithCallable([$classMethod], function (Node $node): ?Node {
            if (! $node instanceof Return_ || ! $node->expr instanceof ConstFetch || ! $this->isTrue($node->expr)) {
                return null;
            }

            return new Nop();
        });
    }

    private function removeFirstInstanceOf(ClassMethod $classMethod): ClassMethod
    {
        if (! isset($classMethod->stmts[0])) {
            return $classMethod;
        }

        if (! $classMethod->stmts[0] instanceof If_) {
            return $classMethod;
        }

        /** @var If_ $ifNode */
        $ifNode = $classMethod->stmts[0];
        if (! $ifNode->stmts[0] instanceof Return_) {
            return $classMethod;
        }

        /** @var Return_ $returnNode */
        $returnNode = $ifNode->stmts[0];
        if (! $returnNode->expr instanceof ConstFetch) {
            return $classMethod;
        }

        $constFetchNode = $returnNode->expr;
        if ($constFetchNode->name->toString() === null) {
            unset($classMethod->stmts[0]);
        }

        return $classMethod;
    }
}
