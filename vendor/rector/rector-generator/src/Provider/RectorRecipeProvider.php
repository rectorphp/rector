<?php

declare (strict_types=1);
namespace Rector\RectorGenerator\Provider;

use Rector\RectorGenerator\Exception\ConfigurationException;
use Rector\RectorGenerator\ValueObject\Option;
use Rector\RectorGenerator\ValueObject\RectorRecipe;
final class RectorRecipeProvider
{
    /**
     * @var \Rector\RectorGenerator\ValueObject\RectorRecipe|null
     */
    private $rectorRecipe = null;
    /**
     * Configure in the rector-recipe.php config
     *
     * @param array<Option::*, mixed> $rectorRecipeConfiguration
     */
    public function __construct(array $rectorRecipeConfiguration = [])
    {
        // no configuration provided - due to autowiring
        if ($rectorRecipeConfiguration === []) {
            return;
        }
        $rectorRecipe = new RectorRecipe($rectorRecipeConfiguration[Option::PACKAGE], $rectorRecipeConfiguration[Option::NAME], $rectorRecipeConfiguration[Option::NODE_TYPES], $rectorRecipeConfiguration[Option::DESCRIPTION], $rectorRecipeConfiguration[Option::CODE_BEFORE], $rectorRecipeConfiguration[Option::CODE_AFTER]);
        // optional parameters
        if (isset($rectorRecipeConfiguration[Option::CONFIGURATION])) {
            $rectorRecipe->setConfiguration($rectorRecipeConfiguration[Option::CONFIGURATION]);
        }
        if (isset($rectorRecipeConfiguration[Option::RESOURCES])) {
            $rectorRecipe->setResources($rectorRecipeConfiguration[Option::RESOURCES]);
        }
        if (isset($rectorRecipeConfiguration[Option::SET_FILE_PATH])) {
            $rectorRecipe->setSetFilePath($rectorRecipeConfiguration[Option::SET_FILE_PATH]);
        }
        $this->rectorRecipe = $rectorRecipe;
    }
    public function provide() : RectorRecipe
    {
        if (!$this->rectorRecipe instanceof RectorRecipe) {
            throw new ConfigurationException('Make sure the "rector-recipe.php" config file is imported and parameter set. Are you sure its in your main config?');
        }
        return $this->rectorRecipe;
    }
}
