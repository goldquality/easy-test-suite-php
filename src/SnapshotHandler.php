<?php

namespace GoldQuality\PHPUnitSnapshots;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionObject;
use RuntimeException;

class SnapshotHandler
{
    private string $filePath;

    public function __construct(
        private string $classWithNamespace,
        private string $testName,
        private bool   $isDirStyle = true
    )
    {
        $this->filePath = $this->generateFilePath();
    }


    protected function generateFilePath(): string
    {
        $object = new ReflectionClass($this->classWithNamespace);

        $filename = $object->getFileName();
        $filenameWithoutExt = str_replace('.php', '', $filename);

        $pattern = $this->isDirStyle ? "%s_snapshots/%s.json" : "%s/%s/%s_%s.json";

        return sprintf(
            $pattern,
            $filenameWithoutExt,
            $this->testName
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

        TestCase::markTestIncomplete("⚠️Snapshot created {$this->filePath}");
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
