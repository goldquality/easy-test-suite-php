<?php

declare(strict_types=1);

namespace GoldQuality\EasyTestSuite;

use Coduo\PHPMatcher\Backtrace;
use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use Illuminate\Testing\TestResponse;
use JsonException;

trait SnapshotAssertTrait
{
    use PHPMatcherAssertions;

    protected ?Backtrace $backtrace = null;
    protected bool $isDirStyleSnapshotsSaving = true;

    protected string $absoluteTestsNamespacePath = 'Tests/';
    protected string $absoluteTestsPath = '/app/tests';

    protected function setBacktrace(Backtrace $backtrace) : void
    {
        $this->backtrace = $backtrace;
    }

    protected function initSnapshotHandler(): SnapshotHandler
    {
        return new SnapshotHandler(
            $this->absoluteTestsNamespacePath,
            $this->absoluteTestsPath,
            static::class,
            $this->getTestName(),
            $this->isDirStyleSnapshotsSaving
        );
    }

    /**
     * @throws JsonException
     */
    protected function encodeSnapshotData(array $data): string
    {
        return json_encode($data, JSON_THROW_ON_ERROR | JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    }

    public function assertResponseSnapshot(TestResponse $response): void
    {
        $this->assertSnapshot($response->collect()->toArray());
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

    abstract public static function markTestSkipped(string $message = ''): never;

    protected function getTestName(): string
    {
        return $this->name() . '_' . $this->dataName();
    }
}
