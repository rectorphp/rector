<?php declare(strict_types=1);

namespace Rector\ReflectionDocBlock\DocBlock;

use phpDocumentor\Reflection\DocBlock;
use phpDocumentor\Reflection\DocBlock\DescriptionFactory;
use phpDocumentor\Reflection\DocBlock\TagFactory;
use phpDocumentor\Reflection\DocBlockFactory as PhpDocumentorDocBlockFactory;
use phpDocumentor\Reflection\TypeResolver;
use PhpParser\Node;

final class DocBlockFactory
{
    /**
     * @var PhpDocumentorDocBlockFactory
     */
    private $phpDocumentorDocBlockFactory;

    public function __construct(
        TagFactory $tagFactory,
        PhpDocumentorDocBlockFactory $phpDocumentorDocBlockFactory,
        DescriptionFactory $descriptionFactory,
        TypeResolver $typeResolver
    ) {
        $this->phpDocumentorDocBlockFactory = $phpDocumentorDocBlockFactory;

        // cannot move to services.yml, because it would cause circular dependency exception
        $tagFactory->addService($descriptionFactory);
        $tagFactory->addService($typeResolver);
    }

    public function createFromNode(Node $node): DocBlock
    {
        $docBlock = $node->getDocComment() ? $node->getDocComment()->getText() : ' ';

        return $this->phpDocumentorDocBlockFactory->create($docBlock);
    }
}
