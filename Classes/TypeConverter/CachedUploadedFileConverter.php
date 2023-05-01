<?php
declare(strict_types=1);

namespace Sitegeist\FusionForm\Upload\TypeConverter;

use Neos\Flow\Utility\Algorithms;
use Neos\Http\Factories\FlowUploadedFile;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Property\Exception\TypeConverterException;
use Neos\Flow\Property\PropertyMappingConfigurationInterface;
use Neos\Flow\Property\TypeConverter\AbstractTypeConverter;
use Sitegeist\FusionForm\Upload\Domain\CachedUploadedFile;
use Sitegeist\FusionForm\Upload\Storage\CachedUploadedFileStorage;

class CachedUploadedFileConverter extends AbstractTypeConverter
{
    /**
     * @var CachedUploadedFileStorage
     * @Flow\Inject
     */
    protected $cachedUploadedFileStorage;

    /**
     * The source types this converter can convert.
     *
     * @var array<string>
     * @api
     */
    protected $sourceTypes = ['string', FlowUploadedFile::class];

    /**
     * The target type this converter can convert to.
     *
     * @var string
     * @api
     */
    protected $targetType = CachedUploadedFile::class;

    public function convertFrom($source, $targetType, array $convertedChildProperties = [], PropertyMappingConfigurationInterface $configuration = null)
    {
        if ($source instanceof FlowUploadedFile) {
            if ($source->getSize() > 0) {
                $uploadedFile = $this->cachedUploadedFileStorage->store($source);
                return $uploadedFile;
            } elseif ($source->getOriginallySubmittedResource()) {
                $identifier = $source->getOriginallySubmittedResource();
                if (is_array($identifier)) {
                    $identifier = $identifier['__identity'];
                }
                return $this->cachedUploadedFileStorage->retrieve($identifier);
            }
        }
        if (is_string($source) && !empty($source)) {
            return $this->cachedUploadedFileStorage->retrieve($source);
        }
        return null;
    }
}
