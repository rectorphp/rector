<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory;

use Doctrine\ORM\Mapping\Annotation;
use PhpParser\Node;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Contract\MultiPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\Contract\PhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\AbstractTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\PHPDI\PHPDIInjectTagValueNode;

final class MultiPhpDocNodeFactory extends AbstractPhpDocNodeFactory implements PhpDocNodeFactoryInterface, MultiPhpDocNodeFactoryInterface
{
    /**
     * @var ArrayPartPhpDocTagPrinter
     */
    private $arrayPartPhpDocTagPrinter;

    /**
     * @var TagValueNodePrinter
     */
    private $tagValueNodePrinter;

    public function __construct(
        ArrayPartPhpDocTagPrinter $arrayPartPhpDocTagPrinter,
        TagValueNodePrinter $tagValueNodePrinter
    ) {
        $this->arrayPartPhpDocTagPrinter = $arrayPartPhpDocTagPrinter;
        $this->tagValueNodePrinter = $tagValueNodePrinter;
    }

    /**
     * @return array<class-string<AbstractTagValueNode>, class-string<Annotation>>
     */
    public function getTagValueNodeClassesToAnnotationClasses(): array
    {
        return [
            // tag value node class => annotation class

            // JMS
            PHPDIInjectTagValueNode::class => 'DI\Annotation\Inject',
        ];
    }

    public function createFromNodeAndTokens(
        Node $node,
        TokenIterator $tokenIterator,
        string $annotationClass
    ): ?PhpDocTagValueNode {
        $annotation = $this->nodeAnnotationReader->readAnnotation($node, $annotationClass);
        if ($annotation === null) {
            return null;
        }

        $tagValueNodeClassesToAnnotationClasses = $this->getTagValueNodeClassesToAnnotationClasses();
        $tagValueNodeClass = array_search($annotationClass, $tagValueNodeClassesToAnnotationClasses, true);
        if ($tagValueNodeClass === false) {
            return null;
        }

        $items = $this->annotationItemsResolver->resolve($annotation);
        $content = $this->annotationContentResolver->resolveFromTokenIterator($tokenIterator);

        return new $tagValueNodeClass(
            $this->arrayPartPhpDocTagPrinter,
            $this->tagValueNodePrinter,
            $items,
            $content
        );
    }
}
