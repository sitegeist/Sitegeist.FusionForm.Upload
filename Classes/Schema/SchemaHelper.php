<?php
declare(strict_types=1);

namespace Sitegeist\FusionForm\Upload\Schema;

use Neos\Eel\ProtectedContextAwareInterface;
use Neos\Fusion\Form\Runtime\Domain\SchemaInterface;
use Neos\Fusion\Form\Runtime\Helper\SchemaDefinition;
use Sitegeist\FusionForm\Upload\Domain\CachedUploadedFile;
use Sitegeist\FusionForm\Upload\Domain\CachedUploadFileCollection;

class SchemaHelper implements ProtectedContextAwareInterface
{

    /**
     * Create a string schema
     * @return SchemaInterface
     */
    public function upload(): SchemaInterface
    {
        return new SchemaDefinition(CachedUploadedFile::class);
    }

    /**
     * @return SchemaInterface
     */
    public function uploads(): SchemaInterface
    {
        return new SchemaDefinition(CachedUploadFileCollection::class);
    }

    /**
     * @param string $methodName
     * @return bool
     */
    public function allowsCallOfMethod($methodName)
    {
        return true;
    }
}
