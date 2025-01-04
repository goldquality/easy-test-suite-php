<?php

declare(strict_types=1);

namespace GoldQuality\EasyTestSuite;

use Coduo\PHPMatcher\PHPUnit\PHPMatcherAssertions;
use JsonException;

trait SnapshotAssertTrait
{
    use PHPMatcherAssertions;

    protected bool $isDirStyle = true;

    protected function getAbsoluteTestsPath(): string
    {
        return '/app/tests';
    }

    protected function getTestsRootNamespace(): string
    {
        if (class_exists(\Illuminate\Foundation\Application::class)) {
            return 'Tests/';
        }

        if (class_exists(\Symfony\Component\HttpKernel\Kernel::class)) {
            return 'App/Tests/';
        }

        throw new \RuntimeException('Cannot identify framework, please override this method. Check autoload section in composer.json');
    }

    protected function initSnapshotHandler(): SnapshotHandler
    {
        return new SnapshotHandler(
            $this->getTestsRootNamespace(),
            $this->getAbsoluteTestsPath(),
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
