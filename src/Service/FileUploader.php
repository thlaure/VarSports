<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private SluggerInterface $slugger,
        private LoggerInterface $logger,
        private Filesystem $filesystem,
    ) {
    }

    public function upload(UploadedFile $file, string $targetDirectory): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        if (!$this->filesystem->exists($targetDirectory)) {
            $this->filesystem->mkdir($targetDirectory, 0700);
        }

        try {
            $file->move($targetDirectory, $fileName);

            return $fileName;
        } catch (FileException $e) {
            $this->logger->error($e->getMessage());
            throw $e;
        }
    }
}
