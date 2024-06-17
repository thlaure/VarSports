<?php

namespace App\Service;

use App\Constant\Constraint;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileChecker
{
    public function __construct(
        private LoggerInterface $logger
    ) {
    }

    /**
     * @param array<string> $allowedExtensions
     */
    public function isExtensionValid(string $fileName, array $allowedExtensions): bool
    {
        $extension = pathinfo($fileName, PATHINFO_EXTENSION);
        if (!in_array($extension, $allowedExtensions)) {
            $this->logger->error('Extension not allowed: '.$extension);

            return false;
        }

        return true;
    }

    /**
     * @param array<string> $allowedMimeTypes
     */
    public function isMimeTypeValid(UploadedFile $file, array $allowedMimeTypes): bool
    {
        $mimeType = $file->getMimeType();
        if (!in_array($mimeType, $allowedMimeTypes)) {
            $this->logger->error('Mime type not allowed: '.$mimeType);

            return false;
        }

        return true;
    }

    public function isSizeValid(UploadedFile $file, int $maxFileSize): bool
    {
        if ($file->getSize() > $maxFileSize || 0 === $file->getSize()) {
            $this->logger->error('File too large: '.$file->getSize());

            return false;
        }

        return true;
    }

    /**
     * @param string[] $allowedMimeTypesByExtension
     */
    public function isMimeTypeCorrespondingToExtension(UploadedFile $file, array $allowedMimeTypesByExtension): bool
    {
        $extension = $file->getClientOriginalExtension();
        $mimeType = $file->getMimeType();
        if (!isset($allowedMimeTypesByExtension[$extension]) || $mimeType !== $allowedMimeTypesByExtension[$extension]) {
            $this->logger->error('Mime type and extension not matching: '.$mimeType);

            return false;
        }

        return true;
    }

    public function checkImageIsValid(UploadedFile $file): bool
    {
        $fileName = $file->getClientOriginalName();
        $extensionIsValid = $this->isExtensionValid($fileName, Constraint::IMAGE_ALLOWED_EXTENSIONS);
        $mimeTypeIsValid = $this->isMimeTypeValid($file, Constraint::IMAGE_ALLOWED_MIME_TYPES);
        $sizeIsValid = $this->isSizeValid($file, Constraint::IMAGE_MAX_FILE_SIZE);
        $mimeTypeCorrespondToExtension = $this->isMimeTypeCorrespondingToExtension($file, Constraint::IMAGE_ALLOWED_MIME_TYPE_BY_EXTENSION);
        if (!$extensionIsValid || !$mimeTypeIsValid || !$sizeIsValid || !$mimeTypeCorrespondToExtension) {
            return false;
        }

        return true;
    }
}
