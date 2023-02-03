<?php
declare(strict_types=1);

namespace Sitegeist\FusionForm\Upload\Storage;

use Neos\Cache\Frontend\VariableFrontend;
use Neos\Flow\Annotations as Flow;
use Neos\Flow\Utility\Algorithms;
use Neos\Http\Factories\FlowUploadedFile;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Sitegeist\FusionForm\Upload\Domain\CachedUploadedFile;

class CachedUploadedFileStorage
{
    /**
     * @var VariableFrontend
     * @Flow\Inject
     */
    protected $cache;

    /**
     * @var StreamFactoryInterface
     * @Flow\Inject
     */
    protected $streamFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function retrieve(string $identifier): ?CachedUploadedFile
    {
        if ($cached = $this->cache->get($identifier)) {
            $this->logger?->info('get', [$identifier, $cached]);
            return new CachedUploadedFile(
                $identifier,
                $this->streamFactory->createStream($cached['content']),
                $cached['size'],
                $cached['error'],
                $cached['filename'],
                $cached['mediaType']
            );
        }
        $this->logger?->info('get', [$identifier, null]);
        return null;
    }

    public function store(FlowUploadedFile $file): CachedUploadedFile
    {
        $file->getStream()->rewind();
        $identifier = Algorithms::generateUUID();
        $data = [
            'content' => $file->getStream()->getContents(),
            'size' => $file->getSize(),
            'error' => $file->getError(),
            'filename' => $file->getClientFilename(),
            'mediaType' => $file->getClientMediaType()
        ];
        $this->logger?->info('store', [$identifier, $data]);
        $this->cache->set($identifier, $data);
        $file->getStream()->rewind();

        return new CachedUploadedFile(
            $identifier,
            $file->getStream(),
            $data['size'],
            $data['error'],
            $data['filename'],
            $data['mediaType']
        );
    }

    public function remove(CachedUploadedFile $file): void
    {
        $this->cache->remove($file->getPersistenceObjectIdentifier());
    }
}
