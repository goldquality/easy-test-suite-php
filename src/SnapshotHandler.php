<?php

namespace GoldQuality\EasyTestSuite;

use PHPUnit\Framework\TestCase;
use RuntimeException;

class SnapshotHandler
{
    private string $filePath;

    public function __construct(
        private string $namespaceRoot,
        private string $pathRoot,
        private string $classWithNamespace,
        private string $testFileName,
        private bool $isDirStyle = true
    )
    {
        $this->filePath = $this->generateFilePath();
    }

    protected function generateFilePath(): string
    {
        $testFilePath = str_replace(['\\', $this->namespaceRoot], ['/', ''], $this->classWithNamespace);
        $testDirPath = dirname($testFilePath);
        $testDirName = basename($testFilePath);

        $pattern = $this->isDirStyle ? "%s/%s/%s_snapshots/%s.json" : "%s/%s/%s_%s.json";

        return sprintf(
            $pattern,
            $this->pathRoot,
            $testDirPath,
            $testDirName,
            $this->testFileName
        );
    }

    public function saveIfPossible(string $jsonContent): void
    {
        if (file_exists($this->filePath)) {
            return;
        }

        $concurrentDirectory = dirname($this->filePath);

        // Double-check if the directory exists
        if (!is_dir($concurrentDirectory) && !mkdir($concurrentDirectory, 0777, true) && !is_dir($concurrentDirectory)) {
            throw new RuntimeException(sprintf('Directory "%s" was not created', $concurrentDirectory));
        }

        $result = file_put_contents($this->filePath, "⚠️DELETE THIS ROW ⚠️\n{$jsonContent}");
        if ($result === false) {
            throw new RuntimeException("Cannot save snapshot file {$this->filePath}");
        }

        TestCase::markTestSkipped("⚠️Snapshot created {$this->filePath}");
    }

    public function getSnapshotContent(): string
    {
        $snapshotContent = file_get_contents($this->filePath);
        if ($snapshotContent === false) {
            throw new RuntimeException($this->filePath);
        }

        return $snapshotContent;
    }

    public function getFilePath(): string
    {
        return $this->filePath;
    }
}
