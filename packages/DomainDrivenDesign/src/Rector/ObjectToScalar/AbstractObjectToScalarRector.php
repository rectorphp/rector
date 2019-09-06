<?php declare(strict_types=1);

namespace Rector\DomainDrivenDesign\Rector\ObjectToScalar;

use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;

abstract class AbstractObjectToScalarRector extends AbstractRector
{
    /**
     * @var string[]
     */
    protected $valueObjectsToSimpleTypes = [];

    /**
     * @var DocBlockManipulator
     */
    protected $docBlockManipulator;

    /**
     * @var BetterNodeFinder
     */
    protected $betterNodeFinder;

    /**
     * @param string[] $valueObjectsToSimpleTypes
     */
    public function __construct(array $valueObjectsToSimpleTypes = [])
    {
        $this->valueObjectsToSimpleTypes = $valueObjectsToSimpleTypes;
    }

    /**
     * @required
     */
    public function autowireAbstractObjectToScalarRectorDependencies(
        DocBlockManipulator $docBlockManipulator,
        BetterNodeFinder $betterNodeFinder
    ): void {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->betterNodeFinder = $betterNodeFinder;
    }
}
