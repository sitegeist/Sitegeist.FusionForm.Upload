<?php
declare(strict_types=1);

namespace Sitegeist\FusionForm\Upload\TypeConverter;

use Neos\Error\Messages\Error;
use Neos\Http\Factories\FlowUploadedFile;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Property\Exception;
use Neos\Flow\Property\Exception\TypeConverterException;
use Neos\Flow\Property\PropertyMappingConfigurationInterface;
use Neos\Flow\Property\TypeConverter\AbstractTypeConverter;
use Sitegeist\FusionForm\Upload\Domain\CachedUploadedFile;
use Sitegeist\FusionForm\Upload\Domain\CachedUploadedFileCollection;
use Sitegeist\FusionForm\Upload\Storage\CachedUploadedFileStorage;

class CachedUploadedFileCollectionConverter extends AbstractTypeConverter
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
    protected $sourceTypes = ['array'];

    /**
     * The target type this converter can convert to.
     *
     * @var string
     * @api
     */
    protected $targetType = CachedUploadedFileCollection::class;

    public function convertFrom($source, $targetType, array $convertedChildProperties = [], ?PropertyMappingConfigurationInterface $configuration = null)
    {
        if (is_array($source)) {
            $files = [];

            // new submission replace the whole collection
            if ($source[0] instanceof FlowUploadedFile && $source[0]->getSize() > 0) {
                foreach ($source as $item) {
                    if ($item instanceof FlowUploadedFile && $item->getSize() > 0) {
                        $files[] = $this->cachedUploadedFileStorage->store($item);
                    }
                }
            } else {
                foreach ($source as $item) {
                    if ($item instanceof FlowUploadedFile) {
                        if ($item->getOriginallySubmittedResource()) {
                            $identifier = $item->getOriginallySubmittedResource();
                            if (is_string($identifier)) {
                                $files[] = $this->cachedUploadedFileStorage->retrieve($identifier);
                            }
                            if (is_array($identifier) && array_key_exists('__identity', $identifier)) {
                                $files[] = $this->cachedUploadedFileStorage->retrieve($identifier['__identity']);
                            }
                        }
                    } elseif (is_array($item) && array_key_exists('originallySubmittedResource', $item)) {
                        if (is_array($item['originallySubmittedResource']) && array_key_exists('__identity', $item['originallySubmittedResource'])) {
                            $files[] = $this->cachedUploadedFileStorage->retrieve($item['originallySubmittedResource']['__identity']);
                        }
                    } elseif (is_string($item) && !empty($item)) {
                        $files[] = $this->cachedUploadedFileStorage->retrieve($item);
                    }
                }
            }
            return new CachedUploadedFileCollection(... $files);
        }
        throw new TypeConverterException('cannot convert');
    }
}
