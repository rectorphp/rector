<?php

declare(strict_types=1);

namespace Rector\StaticTypeMapper\Mapper;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Scalar\String_;
use PHPStan\Type\Type;
use Rector\Core\Exception\NotImplementedYetException;
use Rector\StaticTypeMapper\Contract\PhpParser\PhpParserNodeMapperInterface;

final class PhpParserNodeMapper
{
    /**
     * @var PhpParserNodeMapperInterface[]
     */
    private $phpParserNodeMappers = [];

    /**
     * @param PhpParserNodeMapperInterface[] $phpParserNodeMappers
     */
    public function __construct(array $phpParserNodeMappers)
    {
        $this->phpParserNodeMappers = $phpParserNodeMappers;
    }

    public function mapToPHPStanType(Node $node): Type
    {
        foreach ($this->phpParserNodeMappers as $phpParserNodeMapper) {
            if (! is_a($node, $phpParserNodeMapper->getNodeType())) {
                continue;
            }

            // do not let Expr collect all the types
            // note: can be solve later with priorities on mapper interface, making this last
            if ($phpParserNodeMapper->getNodeType() === Expr::class && is_a($node, String_::class)) {
                continue;
            }

            return $phpParserNodeMapper->mapToPHPStan($node);
        }

        throw new NotImplementedYetException(get_class($node));
    }
}
