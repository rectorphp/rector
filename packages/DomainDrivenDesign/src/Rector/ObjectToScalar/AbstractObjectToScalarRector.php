<?php declare(strict_types=1);

namespace Rector\DomainDrivenDesign\Rector\ObjectToScalar;

use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\DocBlockManipulator;
use Rector\NodeTypeResolver\PhpDoc\NodeAnalyzer\NamespaceAnalyzer;
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
     * @var NamespaceAnalyzer
     */
    protected $namespaceAnalyzer;

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
        BetterNodeFinder $betterNodeFinder,
        NamespaceAnalyzer $namespaceAnalyzer
    ): void {
        $this->docBlockManipulator = $docBlockManipulator;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->namespaceAnalyzer = $namespaceAnalyzer;
    }
}
