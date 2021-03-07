<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Class_;

use Doctrine\ORM\Mapping\Table;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Contract\SpecificPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_\TableTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;

final class TablePhpDocNodeFactory extends AbstractPhpDocNodeFactory implements SpecificPhpDocNodeFactoryInterface
{
    /**
     * @var string
     * @see https://regex101.com/r/HKjBVt/1
     */
    private const SPACE_BEFORE_CLOSING_BRACKET_REGEX = '#,(\s+)?}$#m';

    /**
     * @var IndexPhpDocNodeFactory
     */
    private $indexPhpDocNodeFactory;

    /**
     * @var UniqueConstraintPhpDocNodeFactory
     */
    private $uniqueConstraintPhpDocNodeFactory;

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
        TagValueNodePrinter $tagValueNodePrinter,
        IndexPhpDocNodeFactory $indexPhpDocNodeFactory,
        UniqueConstraintPhpDocNodeFactory $uniqueConstraintPhpDocNodeFactory
    ) {
        $this->indexPhpDocNodeFactory = $indexPhpDocNodeFactory;
        $this->uniqueConstraintPhpDocNodeFactory = $uniqueConstraintPhpDocNodeFactory;
        $this->arrayPartPhpDocTagPrinter = $arrayPartPhpDocTagPrinter;
        $this->tagValueNodePrinter = $tagValueNodePrinter;
    }

    /**
     * @return string[]
     */
    public function getClasses(): array
    {
        return ['Doctrine\ORM\Mapping\Table'];
    }

    public function createFromNodeAndTokens(
        Node $node,
        TokenIterator $tokenIterator,
        string $annotationClass
    ): ?PhpDocTagValueNode {
        if (! $node instanceof Class_) {
            throw new ShouldNotHappenException();
        }

        $table = $this->nodeAnnotationReader->readClassAnnotation($node, $annotationClass);
        if (! $table instanceof Table) {
            return null;
        }

        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);

        $indexesContent = $this->annotationContentResolver->resolveNestedKey($annotationContent, 'indexes');
        $indexTagValueNodes = $this->indexPhpDocNodeFactory->createIndexTagValueNodes(
            $table->indexes,
            $indexesContent
        );

        $indexesAroundSpaces = $this->matchCurlyBracketAroundSpaces($indexesContent);

        $haveIndexesFinalComma = (bool) Strings::match($indexesContent, self::SPACE_BEFORE_CLOSING_BRACKET_REGEX);
        $uniqueConstraintsContent = $this->annotationContentResolver->resolveNestedKey(
            $annotationContent,
            'uniqueConstraints'
        );

        $uniqueConstraintAroundSpaces = $this->matchCurlyBracketAroundSpaces($uniqueConstraintsContent);

        $uniqueConstraintTagValueNodes = $this->uniqueConstraintPhpDocNodeFactory->createUniqueConstraintTagValueNodes(
            $table->uniqueConstraints,
            $uniqueConstraintsContent
        );

        $haveUniqueConstraintsFinalComma = (bool) Strings::match(
            $uniqueConstraintsContent,
            self::SPACE_BEFORE_CLOSING_BRACKET_REGEX
        );

        return new TableTagValueNode(
            $this->arrayPartPhpDocTagPrinter,
            $this->tagValueNodePrinter,
            $table->name,
            $table->schema,
            $indexTagValueNodes,
            $uniqueConstraintTagValueNodes,
            $table->options,
            $annotationContent,
            $haveIndexesFinalComma,
            $haveUniqueConstraintsFinalComma,
            $indexesAroundSpaces,
            $uniqueConstraintAroundSpaces
        );
    }
}
