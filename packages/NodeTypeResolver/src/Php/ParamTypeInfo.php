<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Php;

use Rector\Php\TypeAnalyzer;

final class ParamTypeInfo extends AbstractTypeInfo
{
    /**
     * @var string[]
     */
    protected $typesToRemove = ['void', 'real'];

    /**
     * @var bool
     */
    protected $isAlias = false;

    /**
     * @var string
     */
    private $name;

    /**
     * @param string[] $types
     * @param string[] $fqnTypes
     */
    public function __construct(
        string $name,
        TypeAnalyzer $typeAnalyzer,
        array $types,
        array $fqnTypes = [],
        bool $isAlias = false
    ) {
        $this->name = $this->normalizeName($name);
        $this->isAlias = $isAlias;

        parent::__construct($types, $typeAnalyzer, $fqnTypes);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
