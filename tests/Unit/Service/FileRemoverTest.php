<?php

namespace App\Test\Unit\Service;

use App\Service\FileRemover;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use Symfony\Component\Filesystem\Filesystem;

class FileRemoverTest extends TestCase
{
    private FileRemover $fileRemover;
    private LoggerInterface $logger;
    private string $imageTestName;
    private string $targetDirectory;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
        $this->fileRemover = new FileRemover($this->logger, new Filesystem());

        $this->targetDirectory = 'tests/Unit/Service/fixtures';
        $this->imageTestName = 'image_test_copy.png';
        copy($this->targetDirectory.'/image_test.png', $this->targetDirectory.'/'.$this->imageTestName);
    }

    // Tests remove
    public function testRemove(): void
    {
        $this->fileRemover->remove($this->imageTestName, $this->targetDirectory);

        $this->logger->expects($this->never())
            ->method('error');

        $this->assertFileDoesNotExist($this->targetDirectory.'/'.$this->imageTestName);
    }
}
