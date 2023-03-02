<?php
declare(strict_types=1);

namespace Sitegeist\FusionForm\Upload\Validation\Validator;

/*
 * This file is part of the Neos.Fusion.Form package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Validation\Validator\AbstractValidator;
use Neos\Flow\ResourceManagement\PersistentResource;
use Neos\Utility\MediaTypes;
use Psr\Http\Message\UploadedFileInterface;

/**
 * The given $value is valid if it is an Collection \Psr\Http\Message\UploadedFileInterface
 * Note: a value of NULL or empty string ('') is considered valid
 */
class UploadedFileValidator extends AbstractValidator
{
    /**
     * This contains the supported options, each being an array of:
     *
     * 0 => default value
     * 1 => description
     * 2 => type
     * 3 => required (boolean, optional)
     *
     * @var array
     */
    protected $supportedOptions = [
        'allowedExtensions' => [[], 'Array of allowed file extensions', 'array', false],
        'allowedMediaTypes' => [[], 'Array of allowed media types', 'array', false],
        'maximumSize' => [null, 'Maximum size in bytes', 'int', false]
    ];

    /**
     * The given $value is valid if it is an \Psr\Http\Message\UploadedFileInterface
     * Note: a value of NULL or empty string ('') is considered valid
     *
     * @param UploadedFileInterface $upload
     * @return void
     * @api
     */
    protected function isValid($upload)
    {
        if (!$upload instanceof UploadedFileInterface) {
            $this->addError('The given value was not a UploadedFileInterface instance.', 1675443699);
            return;
        }
        if ($this->options['allowedExtensions'] && !in_array(pathinfo($upload->getClientFilename(), PATHINFO_EXTENSION), $this->options['allowedExtensions'])) {
            $this->addError(
                'The file extension has to be one of "%s", "%s" is not allowed.',
                1675443689,
                [
                    implode(', ', $this->options['allowedExtensions']),
                    pathinfo($upload->getClientFilename(), PATHINFO_EXTENSION)
                ]
            );
        }
        if ($this->options['allowedMediaTypes']) {
            $mediaType = $upload->getClientMediaType();
            $successfullMatched = false;
            if ($mediaType === null || $mediaType === '') {
                $this->addError('The file has no media type.', 1677786193);
            } else {
                foreach ($this->options['allowedMediaTypes'] as $mediaRange) {
                    if (MediaTypes::mediaRangeMatches($mediaRange, $mediaType, )) {
                        $successfullMatched = true;
                        break;
                    }
                }
                if ($successfullMatched === false) {
                    $this->addError('The mediaType "%s" is not allowed.', 1677786200, [$mediaType]);
                }
            }
        }
        if ($this->options['maximumSize'] && $upload->getSize() > $this->options['maximumSize']) {
            $this->addError(
                'The file must not be larger than "%s" bytes, "%s" bytes were sent.',
                1677786206,
                [
                    $this->maximumSize,
                    $upload->getSize()
                ]
            );
        }
    }
}
