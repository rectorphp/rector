<?php declare(strict_types=1);

namespace Rector\Rector\Contrib\Nette;

use Rector\Rector\AbstractChangeMethodNameRector;

final class PhpGeneratorDocumentMethodRector extends AbstractChangeMethodNameRector
{
    /**
     * @var string[]
     */
    private $commonMethods = [
        'addDocument' => 'addComment',
        'setDocuments' => 'setComment',
        'getDocuments' => 'getComment',
    ];

    /**
     * @return string[][]
     */
    protected function getPerClassOldToNewMethods(): array
    {
        return [
            'Nette\PhpGenerator\ClassType' => $this->commonMethods,
            'Nette\PhpGenerator\Method' => $this->commonMethods,
            'Nette\PhpGenerator\PhpFile' => $this->commonMethods,
            'Nette\PhpGenerator\Property' => $this->commonMethods,
        ];
    }
}
