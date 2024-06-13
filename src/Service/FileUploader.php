<?php

namespace App\Service;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;

class FileUploader
{
    public function __construct(
        private SluggerInterface $slugger,
        private LoggerInterface $logger
    ) {
    }

    public function upload(UploadedFile $file, string $targetDirectory): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $this->slugger->slug($originalFilename);
        $fileName = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        if (!is_dir($targetDirectory)) {
            $this->logger->error('The target directory does not exist: '.$targetDirectory);
            throw new FileException('The target directory does not exist');
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
