<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class FileRemover
{
    public function __construct(
        private LoggerInterface $logger,
        private Filesystem $filesystem,
    ) {
    }

    public function remove(string $fileName, string $targetDirectory): void
    {
        try {
            if ($this->filesystem->exists($targetDirectory) && $this->filesystem->exists($targetDirectory.'/'.$fileName)) {
                $this->filesystem->remove($targetDirectory.'/'.$fileName);
            }
        } catch (FileException $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }
}
