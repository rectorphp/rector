<?php

declare(strict_types=1);

namespace Rector\DoctrineCodeQuality\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\ArrayItem;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Scalar\String_;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Rector\AbstractRector;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @see https://github.com/doctrine/orm/blob/2.7/UPGRADE.md#query-querybuilder-and-nativequery-parameters-bc-break
 *
 * @see \Rector\DoctrineCodeQuality\Tests\Rector\MethodCall\ChangeQuerySetParametersMethodParameterFromArrayToArrayCollection\ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionRectorTest
 */
final class ChangeQuerySetParametersMethodParameterFromArrayToArrayCollectionRector extends AbstractRector
{
    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [MethodCall::class];
    }

    /**
     * @param MethodCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if ($this->needToSkip($node)) {
            return null;
        }

        $methodArguments = $node->args;
        if (count($methodArguments) !== 1) {
            return null;
        }
        $firstArgument = $methodArguments[0];
        if (! $this->isArrayType($firstArgument->value)) {
            return null;
        }

        unset($node->args);

        $new = $this->getNewArrayCollectionFromSetParametersArgument($firstArgument);

        $node->args = [new Arg($new)];
        return $node;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Change array to ArrayCollection in setParameters method of query builder', [
            new CodeSample(
                <<<'PHP'

use Doctrine\ORM\EntityRepository;

class SomeRepository extends EntityRepository
{
    public function getSomething()
    {
        return $this
            ->createQueryBuilder('sm')
            ->select('sm')
            ->where('sm.foo = :bar')
            ->setParameters([
                'bar' => 'baz'
            ])
            ->getQuery()
            ->getResult()
        ;
    }
}
PHP
                ,
                <<<'PHP'
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query\Parameter;

class SomeRepository extends EntityRepository
{
    public function getSomething()
    {
        return $this
            ->createQueryBuilder('sm')
            ->select('sm')
            ->where('sm.foo = :bar')
            ->setParameters(new ArrayCollection([
                new  Parameter('bar', 'baz'),
            ]))
            ->getQuery()
            ->getResult()
        ;
    }
}
PHP
            ),
        ]);
    }

    private function needToSkip(Node $node): bool
    {
        $classLike = $node->getAttribute(AttributeKey::CLASS_NODE);
        if ($classLike === null) {
            return true;
        }

        //one of the cases when we are in the repo and it's extended from EntityRepository
        if (! $this->isObjectType($classLike, 'Doctrine\ORM\EntityRepository')) {
            return true;
        }
        if (! $this->isObjectType($node->var, 'Doctrine\ORM\EntityRepository')) {
            return true;
        }

        return ! $this->isName($node->name, 'setParameters');
    }

    private function getNewArrayCollectionFromSetParametersArgument(Arg $arg): New_
    {
        /** @var Array_ $arrayExpression */
        $arrayExpression = $arg->value;
        /** @var ArrayItem[] $firstArgumentArrayItems */
        $firstArgumentArrayItems = $arrayExpression->items;
        $arrayCollectionArrayArguments = [];
        foreach ($firstArgumentArrayItems as $firstArgumentArrayItem) {
            if (! $firstArgumentArrayItem->key instanceof String_ || ! $firstArgumentArrayItem->value instanceof String_) {
                throw new ShouldNotHappenException();
            }
            $queryParameter = new New_(new FullyQualified('Doctrine\ORM\Query\Parameter'));
            $queryParameter->args = [new Arg($firstArgumentArrayItem->key), new Arg($firstArgumentArrayItem->value)];
            $arrayCollectionArrayArguments[] = new ArrayItem($queryParameter);
        }

        $arrayCollection = new New_(new FullyQualified('Doctrine\Common\Collections\ArrayCollection'));

        $arrayCollection->args = [new Arg(new Array_($arrayCollectionArrayArguments))];

        return $arrayCollection;
    }
}
