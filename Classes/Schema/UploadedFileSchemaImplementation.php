<?php
declare(strict_types=1);

namespace Sitegeist\FusionForm\Upload\Schema;

use Neos\Error\Messages\Result;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Validation\Error;
use Neos\Fusion\Form\Runtime\Domain\SchemaInterface;
use Neos\Fusion\FusionObjects\AbstractFusionObject;
use Neos\Http\Factories\FlowUploadedFile;
use Neos\Utility\MediaTypes;
use Psr\Http\Message\UploadedFileInterface;
use Sitegeist\FusionForm\Upload\Storage\CachedUploadedFileStorage;

class UploadedFileSchemaImplementation extends AbstractFusionObject implements SchemaInterface
{
    /**
     * @var CachedUploadedFileStorage
     * @Flow\Inject
     */
    protected $cachedUploadedFileStorage;

    /**
     * @var boolean
     */
    protected $allowEmptyValue = true;

    /**
     * @var array
     */
    protected $allowedExtensions = [];

    /**
     * @var array
     */
    protected $allowedMediaTypes = [];

    /**
     * @var int|null
     */
    protected $maximumSize = null;

    public function evaluate()
    {
        $this->allowEmptyValue = (bool) $this->fusionValue('allowEmptyValue');
        $this->allowedExtensions = $this->fusionValue('allowedExtensions');
        $this->allowedMediaTypes = $this->fusionValue('allowedMediaTypes');
        $this->maximumSize = $this->fusionValue('maximumSize');
        return $this;
    }

    public function validate($data): Result
    {
        $result = new Result();
        if (!$data instanceof UploadedFileInterface) {
            $result->addError(new Error('The given value was not a UploadedFileInterface instance.', 1675443699));
            return $result;
        }

        if ($this->allowedExtensions && !in_array(pathinfo($data->getClientFilename(), PATHINFO_EXTENSION), $this->allowedExtensions)) {
            $result->addError(new Error(
                'The file extension has to be one of "%s", "%s" is not allowed.',
                1675443689,
                [
                    implode(', ', $this->allowedExtensions),
                    pathinfo($data->getClientFilename(), PATHINFO_EXTENSION)
                ]
            ));
        }

        if ($this->allowedMediaTypes) {
            $mediaType = $data->getClientMediaType();
            $successfullMatched = false;
            if ($mediaType === null || $mediaType === '') {
                $result->addError(new Error('The file has no media type.', 1677786211));
            } else {
                foreach ($this->allowedMediaTypes as $mediaRange) {
                    if (MediaTypes::mediaRangeMatches($mediaRange, $mediaType, )) {
                        $successfullMatched = true;
                        break;
                    }
                }
                if ($successfullMatched === false) {
                    $result->addError(new Error('The mediaType "%s" is not allowed.', 1677786219, [$mediaType]));
                }
            }
        }

        if ($this->maximumSize && $data->getSize() > $this->maximumSize) {
            $result->addError(new Error(
                'The file must not be larger than "%s" bytes, "%s" bytes were sent.',
                1677786224,
                [
                    $this->maximumSize,
                    $data->getSize()
                ]
            ));
        }

        return $result;
    }

    public function convert($data)
    {
        if ($data instanceof FlowUploadedFile) {
            if ($data->getOriginallySubmittedResource()) {
                $identifier = $data->getOriginallySubmittedResource();
                if (is_array($identifier)) {
                    $identifier = $identifier['__identity'];
                }
                return $this->cachedUploadedFileStorage->retrieve($identifier);
            } elseif ($data->getSize() > 0) {
                $uploadedFile = $this->cachedUploadedFileStorage->store($data);
                return $uploadedFile;
            }
        }

        if (is_string($data) && !empty($data)) {
            return $this->cachedUploadedFileStorage->retrieve($data);
        }

        return null;
    }
}
