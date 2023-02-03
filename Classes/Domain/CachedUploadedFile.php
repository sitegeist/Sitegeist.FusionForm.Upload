<?php
declare(strict_types=1);

namespace Sitegeist\FusionForm\Upload\Domain;

use Neos\Flow\Utility\Algorithms;
use Neos\Http\Factories\FlowUploadedFile;

class CachedUploadedFile extends FlowUploadedFile
{
    /**
     * @var string
     */
    protected string $Persistence_Object_Identifier;

    public function __construct(
        string $uuid,
        $streamOrFile,
        ?int $size,
        int $errorStatus,
        string $clientFilename = null,
        string $clientMediaType = null
    )
    {
        parent::__construct(
            $streamOrFile,
            $size,
            $errorStatus,
            $clientFilename,
            $clientMediaType
        );
        $this->Persistence_Object_Identifier = $uuid;
    }

    /**
     * @return string
     */
    public function getPersistenceObjectIdentifier (): string
    {
        return $this->Persistence_Object_Identifier;
    }
}
