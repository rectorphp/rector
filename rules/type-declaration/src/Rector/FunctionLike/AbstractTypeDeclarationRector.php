<?php

declare(strict_types=1);

namespace Rector\TypeDeclaration\Rector\FunctionLike;

use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Function_;
use Rector\Core\Rector\AbstractRector;
use Rector\TypeDeclaration\PhpParserTypeAnalyzer;
use Rector\VendorLocker\VendorLockResolver;

/**
 * @see https://wiki.php.net/rfc/scalar_type_hints_v5
 * @see https://github.com/nikic/TypeUtil
 * @see https://github.com/nette/type-fixer
 * @see https://github.com/FriendsOfPHP/PHP-CS-Fixer/issues/3258
 */
abstract class AbstractTypeDeclarationRector extends AbstractRector
{
    /**
     * @var PhpParserTypeAnalyzer
     */
    protected $phpParserTypeAnalyzer;

    /**
     * @var VendorLockResolver
     */
    protected $vendorLockResolver;

    /**
     * @required
     */
    public function autowireAbstractTypeDeclarationRector(
        PhpParserTypeAnalyzer $phpParserTypeAnalyzer,
        VendorLockResolver $vendorLockResolver
    ): void {
        $this->phpParserTypeAnalyzer = $phpParserTypeAnalyzer;
        $this->vendorLockResolver = $vendorLockResolver;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [Function_::class, ClassMethod::class];
    }
}
