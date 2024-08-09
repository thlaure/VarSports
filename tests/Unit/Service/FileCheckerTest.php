<?php

namespace App\Test\Unit\Service;

use App\Constant\Constraint;
use App\Service\FileChecker;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class FileCheckerTest extends TestCase
{
    private FileChecker $fileChecker;
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->fileChecker = new FileChecker($this->logger);
    }

    // Tests isExtensionValid
    public function testIsExtensionValidWithAllowedExtension(): void
    {
        $result = $this->fileChecker->isExtensionValid('image.jpg', Constraint::IMAGE_ALLOWED_EXTENSIONS);

        $this->assertTrue($result);
    }

    public function testIsExtensionValidWithNotAllowedExtension(): void
    {
        $result = $this->fileChecker->isExtensionValid('image.php', Constraint::IMAGE_ALLOWED_EXTENSIONS);

        $this->assertFalse($result);
    }

    public function testIsExtensionValidWithNoExtension(): void
    {
        $result = $this->fileChecker->isExtensionValid('image', Constraint::IMAGE_ALLOWED_EXTENSIONS);

        $this->assertFalse($result);
    }

    public function testIsExtensionValidWithUppercaseExtension(): void
    {
        $result = $this->fileChecker->isExtensionValid('image.JPG', Constraint::IMAGE_ALLOWED_EXTENSIONS);

        $this->assertFalse($result);
    }

    // Tests isMimeTypeValid
    public function testIsMimeTypeValidWithAllowedMimeType(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getMimeType')->willReturn('image/jpeg');

        $result = $this->fileChecker->isMimeTypeValid($file, Constraint::IMAGE_ALLOWED_MIME_TYPES);

        $this->assertTrue($result);
    }

    public function testIsMimeTypeValidWithNotAllowedMimeType(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getMimeType')->willReturn('image.php');

        $result = $this->fileChecker->isMimeTypeValid($file, Constraint::IMAGE_ALLOWED_MIME_TYPES);

        $this->assertFalse($result);
    }

    public function testIsMimeTypeValidWithNoMimeType(): void
    {
        $file = $this->createMock(UploadedFile::class);

        $result = $this->fileChecker->isMimeTypeValid($file, Constraint::IMAGE_ALLOWED_MIME_TYPES);

        $this->assertFalse($result);
    }

    public function testIsMimeTypeValidWithUppercaseMimeType(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getMimeType')->willReturn('image/JPG');

        $result = $this->fileChecker->isMimeTypeValid($file, Constraint::IMAGE_ALLOWED_MIME_TYPES);

        $this->assertFalse($result);
    }

    // Tests isSizeValid
    public function testIsSizeValidWithAllowedSize(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getSize')->willReturn(5 * 1024 * 1024);

        $result = $this->fileChecker->isSizeValid($file, Constraint::IMAGE_MAX_FILE_SIZE);

        $this->assertTrue($result);
    }

    public function testIsSizeValidWithNotAllowedSize(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getSize')->willReturn(5 * 1030 * 1024);

        $result = $this->fileChecker->isSizeValid($file, Constraint::IMAGE_MAX_FILE_SIZE);

        $this->assertFalse($result);
    }

    public function testIsSizeValidWithNoSize(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getSize')->willReturn(0);

        $result = $this->fileChecker->isSizeValid($file, Constraint::IMAGE_MAX_FILE_SIZE);

        $this->assertFalse($result);
    }

    // Tests isMimeTypeCorrespondingToExtension
    public function testIsMimeTypeCorrespondingToExtensionWithAllowedMimeType(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalExtension')->willReturn('jpg');
        $file->method('getMimeType')->willReturn('image/jpeg');

        $result = $this->fileChecker->isMimeTypeCorrespondingToExtension($file, Constraint::IMAGE_ALLOWED_MIME_TYPE_BY_EXTENSION);

        $this->assertTrue($result);
    }

    public function testIsMimeTypeCorrespondingToExtensionWithNotAllowedMimeType(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalExtension')->willReturn('jpg');
        $file->method('getMimeType')->willReturn('image/pdf');

        $result = $this->fileChecker->isMimeTypeCorrespondingToExtension($file, Constraint::IMAGE_ALLOWED_MIME_TYPE_BY_EXTENSION);

        $this->assertFalse($result);
    }

    public function testIsMimeTypeCorrespondingToExtensionWithNoMimeType(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalExtension')->willReturn('jpg');

        $result = $this->fileChecker->isMimeTypeCorrespondingToExtension($file, Constraint::IMAGE_ALLOWED_MIME_TYPE_BY_EXTENSION);

        $this->assertFalse($result);
    }

    public function testIsMimeTypeCorrespondingToExtensionWithUppercaseMimeType(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalExtension')->willReturn('jpg');
        $file->method('getMimeType')->willReturn('image/JPEG');

        $result = $this->fileChecker->isMimeTypeCorrespondingToExtension($file, Constraint::IMAGE_ALLOWED_MIME_TYPE_BY_EXTENSION);

        $this->assertFalse($result);
    }

    // Tests checkImageIsValid
    public function testCheckImageIsValidWithAllowedImage(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn('image.jpg');
        $file->method('getClientOriginalExtension')->willReturn('jpg');
        $file->method('getMimeType')->willReturn('image/jpeg');
        $file->method('getSize')->willReturn(5 * 1024 * 1024);

        $result = $this->fileChecker->checkImageIsValid($file);

        $this->assertTrue($result);
    }

    public function testCheckImageIsValidWithNotAllowedImage(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn('image.php');
        $file->method('getClientOriginalExtension')->willReturn('php');
        $file->method('getMimeType')->willReturn('image/jpeg');
        $file->method('getSize')->willReturn(5 * 1024 * 1024);

        $result = $this->fileChecker->checkImageIsValid($file);

        $this->assertFalse($result);
    }

    public function testCheckImageIsValidWithNoExtension(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn('image');
        $file->method('getMimeType')->willReturn('image/jpeg');
        $file->method('getSize')->willReturn(5 * 1024 * 1024);

        $result = $this->fileChecker->checkImageIsValid($file);

        $this->assertFalse($result);
    }

    public function testCheckImageIsValidWithNoMimeType(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn('image.jpg');
        $file->method('getClientOriginalExtension')->willReturn('jpg');
        $file->method('getSize')->willReturn(5 * 1024 * 1024);

        $result = $this->fileChecker->checkImageIsValid($file);

        $this->assertFalse($result);
    }

    public function testCheckImageIsValidWithNoSize(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn('image.jpg');
        $file->method('getClientOriginalExtension')->willReturn('jpg');
        $file->method('getMimeType')->willReturn('image/jpeg');
        $file->method('getSize')->willReturn(0);

        $result = $this->fileChecker->checkImageIsValid($file);

        $this->assertFalse($result);
    }

    public function testCheckImageIsValidWithTallerSize(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn('image.jpg');
        $file->method('getClientOriginalExtension')->willReturn('jpg');
        $file->method('getMimeType')->willReturn('image/jpeg');
        $file->method('getSize')->willReturn(5 * 1030 * 1024);

        $result = $this->fileChecker->checkImageIsValid($file);

        $this->assertFalse($result);
    }

    public function testCheckImageIsValidWithUppercaseMimeType(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn('image.jpg');
        $file->method('getClientOriginalExtension')->willReturn('jpg');
        $file->method('getMimeType')->willReturn('image/JPEG');
        $file->method('getSize')->willReturn(5 * 1024 * 1024);

        $result = $this->fileChecker->checkImageIsValid($file);

        $this->assertFalse($result);
    }

    public function testCheckImageIsValidWithNotAllowedMimeType(): void
    {
        $file = $this->createMock(UploadedFile::class);
        $file->method('getClientOriginalName')->willReturn('image.jpg');
        $file->method('getClientOriginalExtension')->willReturn('jpg');
        $file->method('getMimeType')->willReturn('image/pdf');
        $file->method('getSize')->willReturn(5 * 1024 * 1024);

        $result = $this->fileChecker->checkImageIsValid($file);

        $this->assertFalse($result);
    }
}
