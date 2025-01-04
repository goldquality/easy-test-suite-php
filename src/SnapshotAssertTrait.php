<?php

declare(strict_types=1);

namespace GoldQuality\PHPUnitSnapshots;

use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use JsonException;

trait SnapshotAssertTrait
{
    use PHPMatcherAssertions;

    protected bool $isDirStyle = true;

    protected function initSnapshotHandler(): SnapshotHandler
    {
        return new SnapshotHandler(
            static::class,
            $this->getTestName(),
            $this->isDirStyle
        );
    }

    /**
     * @throws JsonException
     */
    protected function encodeSnapshotData(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function assertSnapshot(array $data): void
    {
        $snapshotHandler = $this->initSnapshotHandler();

        $actualContent = $this->encodeSnapshotData($data);
        $snapshotHandler->saveIfPossible($actualContent);

        $expectedContent = $snapshotHandler->getSnapshotContent();
        $filepath = $snapshotHandler->getFilePath();

        $this->assertMatchesPattern($expectedContent, $actualContent, "Snapshot is not equal. File {$filepath}");
    }

    protected function getTestName(): string
    {
        return $this->name() . '_' . $this->dataName();
    }
}
