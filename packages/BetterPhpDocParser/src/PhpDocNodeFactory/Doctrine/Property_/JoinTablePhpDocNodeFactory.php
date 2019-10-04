<?php declare(strict_types=1);

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
use Rector\Exception\ShouldNotHappenException;

final class JoinTablePhpDocNodeFactory extends AbstractPhpDocNodeFactory
{
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
        $joinColumnValuesTags = $this->createJoinColumnTagValues($annotationContent, $joinTable, 'joinColumns');

        $inverseJoinColumnValuesTags = $this->createJoinColumnTagValues(
            $annotationContent,
            $joinTable,
            'inverseJoinColumns'
        );

        return new JoinTableTagValueNode(
            $joinTable->name,
            $joinTable->schema,
            $joinColumnValuesTags,
            $inverseJoinColumnValuesTags,
            $annotationContent
        );
    }

    /**
     * @return JoinColumnTagValueNode[]
     */
    private function createJoinColumnTagValues(string $annotationContent, JoinTable $joinTable, string $type): array
    {
        $joinColumnContents = $this->matchJoinColumnContents($annotationContent, $type);
        $joinColumnValuesTags = [];

        foreach ($joinTable->joinColumns as $key => $joinColumn) {
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
    private function matchJoinColumnContents(string $annotationContent, string $type): array
    {
        $match = Strings::match($annotationContent, '#' . $type . '=\{(?<content>.*?)\}#sm');
        if (! isset($match['content'])) {
            return [];
        }

        return Strings::matchAll($match['content'], self::JOIN_COLUMN_PATTERN);
    }
}
