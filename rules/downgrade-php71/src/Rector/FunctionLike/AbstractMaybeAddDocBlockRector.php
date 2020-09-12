<?php

declare(strict_types=1);

namespace Rector\DowngradePhp71\Rector\FunctionLike;

use Rector\Core\Contract\Rector\ConfigurableRectorInterface;
use Rector\Core\Rector\AbstractRector;

abstract class AbstractMaybeAddDocBlockRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string
     */
    public const ADD_DOC_BLOCK = '$addDocBlock';

    /**
     * @var bool
     */
    protected $addDocBlock = true;

    public function configure(array $configuration): void
    {
        $this->addDocBlock = $configuration[self::ADD_DOC_BLOCK] ?? true;
    }
}
