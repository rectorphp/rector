<?php declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Attributes\Attribute;

final class Attribute
{
    /**
     * Fully-qualified name
     *
     * @var string
     */
    public const FQN_NAME = 'fqn_name';

    /**
     * @var string
     */
    public const PHP_DOC_NODE_INFO = 'php_doc_node_info';

    /**
     * @var string
     */
    public const TYPE_AS_STRING = 'type_as_string';

    /**
     * @var string
     */
    public const TYPE_AS_ARRAY = 'type_as_array';

    /**
     * @var string
     */
    public const LAST_TOKEN_POSITION = 'last_token_position';

    /**
     * @var string
     */
    public const RESOLVED_NAMES = 'resolved_names';

    /**
     * @var string
     */
    public const ANNOTATION_CLASS = 'annotation_class';
}
