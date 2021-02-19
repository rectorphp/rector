<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\ClassMethod;

use PhpParser\Node;
<<<<<<< HEAD
<<<<<<< HEAD
<<<<<<< HEAD
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name;
=======
>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes
=======
use PhpParser\Node\Name;
>>>>>>> be417ea15... fix accidental interface removal
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Return_;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> be417ea15... fix accidental interface removal
use Rector\Core\ValueObject\PhpVersionFeature;
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
=======
use Rector\Core\Rector\AbstractRector;
>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
=======
use Rector\NodeTypeResolver\PHPStan\Type\TypeFactory;
>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
<<<<<<< HEAD
<<<<<<< HEAD
=======

>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
=======
>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes
 * @see \Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeFromReturnNewRector\ReturnTypeFromReturnNewRectorTest
 */
final class ReturnTypeFromReturnNewRector extends AbstractRector
{
<<<<<<< HEAD
<<<<<<< HEAD
=======
>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes
    /**
     * @var TypeFactory
     */
    private $typeFactory;

    public function __construct(TypeFactory $typeFactory)
    {
        $this->typeFactory = $typeFactory;
    }

<<<<<<< HEAD
=======
>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
=======
>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add return type void to function like without any return', [
            new CodeSample(
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function action()
    {
        return new Response();
    }
}
CODE_SAMPLE
<<<<<<< HEAD
<<<<<<< HEAD
=======

>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
=======
>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes
                ,
                <<<'CODE_SAMPLE'
final class SomeClass
{
    public function action(): Respose
    {
        return new Response();
    }
}
CODE_SAMPLE

<<<<<<< HEAD
<<<<<<< HEAD
            ),
=======
            )
>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
=======
            ),
>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes
        ]);
    }

    /**
<<<<<<< HEAD
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }

        if ($node->returnType !== null) {
            return null;
        }

        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstanceOf((array) $node->stmts, Return_::class);
        if ($returns === []) {
            return null;
        }

        $newTypes = [];
        foreach ($returns as $return) {
            if (! $return->expr instanceof New_) {
                return null;
            }

            $new = $return->expr;
            if (! $new->class instanceof Name) {
                return null;
            }

            $className = $this->getName($new->class);
            $newTypes[] = new ObjectType($className);
        }

        $returnType = $this->typeFactory->createMixedPassedOrUnionType($newTypes);
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType);
        $node->returnType = $returnTypeNode;
=======
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
<<<<<<< HEAD
<<<<<<< HEAD
        // change the node
>>>>>>> ae034a769... [TypeDeclaration] Add ReturnTypeFromReturnNewRector
=======
=======
        if (! $this->isAtLeastPhpVersion(PhpVersionFeature::SCALAR_TYPES)) {
            return null;
        }

        if ($node->returnType !== null) {
            return null;
        }

>>>>>>> be417ea15... fix accidental interface removal
        /** @var Return_[] $returns */
        $returns = $this->betterNodeFinder->findInstanceOf((array) $node->stmts, Return_::class);
        if ($returns === []) {
            return null;
        }

        $newTypes = [];
        foreach ($returns as $return) {
            if (! $return->expr instanceof Node\Expr\New_) {
                return null;
            }

            $new = $return->expr;
            if (! $new->class instanceof Name) {
                return null;
            }

            $className = $this->getName($new->class);
            $newTypes[] = new ObjectType($className);
        }

        $returnType = $this->typeFactory->createMixedPassedOrUnionType($newTypes);
        $returnTypeNode = $this->staticTypeMapper->mapPHPStanTypeToPhpParserNode($returnType);
        $node->returnType = $returnTypeNode;
>>>>>>> ed7f099ba... decouple NodeComparator to compare nodes

        return $node;
    }
}
