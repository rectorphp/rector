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
        $rectorRecipe = new \Rector\RectorGenerator\ValueObject\RectorRecipe($rectorRecipeConfiguration[\Rector\RectorGenerator\ValueObject\Option::PACKAGE], $rectorRecipeConfiguration[\Rector\RectorGenerator\ValueObject\Option::NAME], $rectorRecipeConfiguration[\Rector\RectorGenerator\ValueObject\Option::NODE_TYPES], $rectorRecipeConfiguration[\Rector\RectorGenerator\ValueObject\Option::DESCRIPTION], $rectorRecipeConfiguration[\Rector\RectorGenerator\ValueObject\Option::CODE_BEFORE], $rectorRecipeConfiguration[\Rector\RectorGenerator\ValueObject\Option::CODE_AFTER]);
        // optional parameters
        if (isset($rectorRecipeConfiguration[\Rector\RectorGenerator\ValueObject\Option::CONFIGURATION])) {
            $rectorRecipe->setConfiguration($rectorRecipeConfiguration[\Rector\RectorGenerator\ValueObject\Option::CONFIGURATION]);
        }
        if (isset($rectorRecipeConfiguration[\Rector\RectorGenerator\ValueObject\Option::RESOURCES])) {
            $rectorRecipe->setResources($rectorRecipeConfiguration[\Rector\RectorGenerator\ValueObject\Option::RESOURCES]);
        }
        if (isset($rectorRecipeConfiguration[\Rector\RectorGenerator\ValueObject\Option::SET_FILE_PATH])) {
            $rectorRecipe->setSetFilePath($rectorRecipeConfiguration[\Rector\RectorGenerator\ValueObject\Option::SET_FILE_PATH]);
        }
        $this->rectorRecipe = $rectorRecipe;
    }
    public function provide() : \Rector\RectorGenerator\ValueObject\RectorRecipe
    {
        if (!$this->rectorRecipe instanceof \Rector\RectorGenerator\ValueObject\RectorRecipe) {
            throw new \Rector\RectorGenerator\Exception\ConfigurationException('Make sure the "rector-recipe.php" config file is imported and parameter set. Are you sure its in your main config?');
        }
        return $this->rectorRecipe;
    }
}
