<?php

namespace GoldQuality\PHPUnitSnapshots\Tests;

use GoldQuality\PHPUnitSnapshots\SnapshotAssertTrait;
use PHPUnit\Framework\TestCase;

abstract class ApiTestCase extends TestCase # laravel
//abstract class ApiTestCase extends KernelTestCase # symfony
{
    use SnapshotAssertTrait;

    // Additional helper method
    public function assertResponseSnapshot($response): void
    {
        $this->assertSnapshot($response->collect()->toArray()); # laravel
        //$this->assertSnapshot($response->toArray()); # symfony
    }

}
