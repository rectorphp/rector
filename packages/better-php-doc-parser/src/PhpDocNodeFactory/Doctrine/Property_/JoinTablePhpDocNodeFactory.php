<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Property_;

use Doctrine\ORM\Mapping\JoinTable;
use Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Stmt\Property;
use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use PHPStan\PhpDocParser\Parser\TokenIterator;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\JoinColumnTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_\JoinTableTagValueNode;
use Rector\BetterPhpDocParser\PhpDocNodeFactory\AbstractPhpDocNodeFactory;
use Rector\Core\Exception\ShouldNotHappenException;

final class JoinTablePhpDocNodeFactory extends AbstractPhpDocNodeFactory
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
     */
    private const JOIN_COLUMN_PATTERN = '#(?<tag>@(ORM\\\\)?JoinColumn)\((?<content>.*?)\),?#si';

    /**
     * @var JoinColumnPhpDocNodeFactory
     */
    private $joinColumnPhpDocNodeFactory;

    public function __construct(JoinColumnPhpDocNodeFactory $joinColumnPhpDocNodeFactory)
    {
        $this->joinColumnPhpDocNodeFactory = $joinColumnPhpDocNodeFactory;
    }

    public function getClass(): string
    {
        return JoinTable::class;
    }

    /**
     * @return JoinTableTagValueNode|null
     */
    public function createFromNodeAndTokens(Node $node, TokenIterator $tokenIterator): ?PhpDocTagValueNode
    {
        if (! $node instanceof Property) {
            throw new ShouldNotHappenException();
        }

        /** @var JoinTable|null $joinTable */
        $joinTable = $this->nodeAnnotationReader->readPropertyAnnotation($node, $this->getClass());
        if ($joinTable === null) {
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

        [$joinColumnsOpeningSpace, $joinColumnsClosingSpace] = $this->matchCurlyBracketOpeningAndClosingSpace(
            $joinColumnsAnnotationContent
        );

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

        [$inverseJoinColumnsOpeningSpace, $inverseJoinColumnsClosingSpace] = $this->matchCurlyBracketOpeningAndClosingSpace(
            $inverseJoinColumnsAnnotationContent
        );

        return new JoinTableTagValueNode(
            $joinTable->name,
            $joinTable->schema,
            $joinColumnValuesTags,
            $inverseJoinColumnValuesTags,
            $annotationContent,
            $joinColumnsOpeningSpace,
            $joinColumnsClosingSpace,
            $inverseJoinColumnsOpeningSpace,
            $inverseJoinColumnsClosingSpace
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

        $joinColumns = $joinTable->{$type};
        foreach ($joinColumns as $key => $joinColumn) {
            $subAnnotation = $joinColumnContents[$key];

            $joinColumnValuesTags[] = $this->joinColumnPhpDocNodeFactory->createFromAnnotationAndAnnotationContent(
                $joinColumn,
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
        return Strings::matchAll($annotationContent, self::JOIN_COLUMN_PATTERN);
    }
}
