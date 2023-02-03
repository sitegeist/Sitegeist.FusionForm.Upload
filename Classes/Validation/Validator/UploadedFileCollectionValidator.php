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
use Psr\Http\Message\UploadedFileInterface;

/**
 * The given $value is valid if it is a Collection of \Psr\Http\Message\UploadedFileInterface
 * Note: a value of NULL or empty string ('') is considered valid
 */
class UploadedFileCollectionValidator extends AbstractValidator
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
        'allowedMediaTypes' => [[], 'Array of allowed media types', 'array', false]
    ];

    /**
     * The given $value is valid if it is an Collection of \Psr\Http\Message\UploadedFileInterface
     * Note: a value of NULL or empty string ('') is considered valid
     *
     * @param UploadedFileInterface[] $uploads
     * @return void
     * @api
     */
    protected function isValid($uploads)
    {
        if (!is_array($uploads) && !($uploads instanceof \Traversable) ) {
            $this->addError('The given value was not a Collection.', 1675443699);
            return;
        }
        $itemValidator = new UploadedFileValidator($this->options);
        foreach ($uploads as $key => $upload) {
            $itemResult = $itemValidator->validate($upload);
            if ($itemResult->hasErrors()) {
                $this->getResult()->forProperty((string)$key)->merge($itemResult);
            }
        }
    }
}
