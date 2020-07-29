<?php

declare(strict_types=1);

namespace Rector\RectorGenerator\ValueObject;

final class RecipeOption
{
    /**
     * @var string
     */
    public const PACKAGE = 'package';

    /**
     * @var string
     */
    public const NAME = 'name';

    /**
     * @var string
     */
    public const NODE_TYPES = 'node_types';

    /**
     * @var string
     */
    public const DESCRIPTION = 'description';

    /**
     * @var string
     */
    public const CODE_BEFORE = 'code_before';

    /**
     * @var string
     */
    public const CODE_AFTER = 'code_after';

    /**
     * @var string
     */
    public const RULE_CONFIGURATION = 'rule_configuration';

    /**
     * @var string
     */
    public const SOURCE = 'source';

    /**
     * @var string
     */
    public const SET = 'set';

    /**
     * @var string
     */
    public const EXTRA_FILE_CONTENT = 'extra_file_content';

    /**
     * @var string
     */
    public const EXTRA_FILE_NAME = 'extra_file_name';
}
