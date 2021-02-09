<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\JoinTable;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\Contract\SpecificPhpDocNodeFactoryInterface;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_\JoinTableTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;

final class JoinTablePhpDocNodeFactory extends AbstractPhpDocNodeFactory implements SpecificPhpDocNodeFactoryInterface
{
    /**
     * @var string
     */
    private const INVERSE_JOIN_COLUMNS = 'inverseJoinColumns';

    /**
     * @var string
     */
    private const JOIN_COLUMNS = 'joinColumns';

    /**
     * @var string
     * @see https://regex101.com/r/5JVito/1
     */
    private const JOIN_COLUMN_REGEX = '#(?<tag>@(ORM\\\\)?JoinColumn)\((?<content>.*?)\),?#si';

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
     * @return string[]
     */
    public function getClasses(): array
    {
        return ['Doctrine\ORM\Mapping\JoinTable'];
    }

    /**
     * @return JoinTableTagValueNode|null
     */
    public function createFromNodeAndTokens(
        Node $node,
        TokenIterator $tokenIterator,
        string $annotationClass
    ): ?PhpDocTagValueNode {
        if (! $node instanceof Property) {
            throw new ShouldNotHappenException();
        }

        $joinTable = $this->nodeAnnotationReader->readPropertyAnnotation($node, $annotationClass);
        if (! $joinTable instanceof JoinTable) {
            return null;
        }

        $annotationContent = $this->resolveContentFromTokenIterator($tokenIterator);

        $joinColumnsAnnotationContent = $this->annotationContentResolver->resolveNestedKey(
            $annotationContent,
            self::JOIN_COLUMNS
        );

        $joinColumnValuesTags = $this->createJoinColumnTagValues(
            $joinColumnsAnnotationContent,
            $joinTable,
            self::JOIN_COLUMNS
        );

        $joinColumnsAroundSpaces = $this->matchCurlyBracketAroundSpaces($joinColumnsAnnotationContent);

        // inversed join columns
        $inverseJoinColumnsAnnotationContent = $this->annotationContentResolver->resolveNestedKey(
            $annotationContent,
            self::INVERSE_JOIN_COLUMNS
        );
        $inverseJoinColumnValuesTags = $this->createJoinColumnTagValues(
            $inverseJoinColumnsAnnotationContent,
            $joinTable,
            self::INVERSE_JOIN_COLUMNS
        );

        $inverseJoinColumnAroundSpaces = $this->matchCurlyBracketAroundSpaces(
            $inverseJoinColumnsAnnotationContent
        );

        return new JoinTableTagValueNode(
            $this->arrayPartPhpDocTagPrinter,
            $this->tagValueNodePrinter,
            $joinTable->name,
            $joinTable->schema,
            $joinColumnValuesTags,
            $inverseJoinColumnValuesTags,
            $annotationContent,
            $joinColumnsAroundSpaces,
            $inverseJoinColumnAroundSpaces
        );
    }

    /**
     * @return JoinColumnTagValueNode[]
     */
    private function createJoinColumnTagValues(string $annotationContent, JoinTable $joinTable, string $type): array
    {
        $joinColumnContents = $this->matchJoinColumnContents($annotationContent);

        $joinColumnValuesTags = [];

        if (! in_array($type, [self::JOIN_COLUMNS, self::INVERSE_JOIN_COLUMNS], true)) {
            throw new ShouldNotHappenException();
        }

        $joinColumns = $type === self::JOIN_COLUMNS ? $joinTable->joinColumns : $joinTable->inverseJoinColumns;

        foreach ($joinColumns as $key => $joinColumn) {
            $subAnnotation = $joinColumnContents[$key];

            $items = $this->annotationItemsResolver->resolve($joinColumn);
            $joinColumnValuesTags[] = new JoinColumnTagValueNode(
                $this->arrayPartPhpDocTagPrinter,
                $this->tagValueNodePrinter,
                $items,
                $subAnnotation['content'],
                $subAnnotation['tag']
            );
        }

        return $joinColumnValuesTags;
    }

    /**
     * @return string[][]
     */
    private function matchJoinColumnContents(string $annotationContent): array
    {
        return Strings::matchAll($annotationContent, self::JOIN_COLUMN_REGEX);
    }
}
