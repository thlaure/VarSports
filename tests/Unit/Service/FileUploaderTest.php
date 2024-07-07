<?php

namespace App\Test\Unit\Service;

use App\Service\FileUploader;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\String\UnicodeString;

class FileUploaderTest extends TestCase
{
    private FileUploader $fileUploader;
    private LoggerInterface $logger;
    private SluggerInterface $slugger;
    private Filesystem $filesystem;
    private string $imageTestName;
    private string $targetDirectory;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->slugger = $this->createMock(SluggerInterface::class);
        $this->filesystem = $this->createMock(Filesystem::class);
        $this->fileUploader = new FileUploader($this->slugger, $this->logger, $this->filesystem);

        $this->targetDirectory = 'tests/Unit/Service/fixtures';
        $this->imageTestName = 'image_test_copy.png';
        copy($this->targetDirectory.'/image_test.png', $this->targetDirectory.'/'.$this->imageTestName);
    }

    // Tests upload
    public function testUpload(): void
    {
        $safeFilename = new UnicodeString($this->imageTestName);

        $file = new UploadedFile(
            $this->targetDirectory.'/'.$this->imageTestName,
            $this->imageTestName,
            null,
            null,
            true
        );

        $this->slugger->expects($this->once())
            ->method('slug')
            ->willReturn($safeFilename);

        $this->logger->expects($this->never())
            ->method('error');

        $uploadedFileName = $this->fileUploader->upload($file, $this->targetDirectory);

        $this->assertFileExists($this->targetDirectory.'/'.$uploadedFileName);

        unlink($this->targetDirectory.'/'.$uploadedFileName);
    }
}
