<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\Provider;

use Rector\RectorGenerator\Exception\ConfigurationException;
use Rector\RectorGenerator\ValueObject\RectorRecipe;

final class RectorRecipeProvider
{
    /**
     * @var string
     */
    private const MESSAGE = 'Make sure the "rector-recipe.php" config file is imported and parameter set. Are you sure its in your main config?';

    /**
     * @var RectorRecipe|null
     */
    private $rectorRecipe;

    /**
     * Parameter must be configured in the rector config
     */
    public function __construct(?RectorRecipe $rectorRecipe = null)
    {
        $this->rectorRecipe = $rectorRecipe;
    }

    public function provide(): RectorRecipe
    {
        if (! $this->rectorRecipe instanceof RectorRecipe) {
            throw new ConfigurationException(self::MESSAGE);
        }

        return $this->rectorRecipe;
    }
}
