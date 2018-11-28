<?php declare(strict_types=1);

namespace Rector\NodeTypeResolver\Php;

final class ParamTypeInfo extends AbstractTypeInfo
{
    /**
     * @var string[]
     */
    protected $typesToRemove = ['void', 'real'];

    /**
     * @var string
     */
    private $name;

    /**
     * @param string[] $types
     * @param string[] $fqnTypes
     */
    public function __construct(string $name, array $types, array $fqnTypes = [])
    {
        $this->name = $this->normalizeName($name);

        parent::__construct($types, $fqnTypes);
    }

    public function getName(): string
    {
        return $this->name;
    }
}
