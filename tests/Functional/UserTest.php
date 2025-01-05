<?php

namespace GoldQuality\PHPUnitSnapshots\Tests\Functional;

use DateTime;
use GoldQuality\PHPUnitSnapshots\Tests\ApiTestCase;

class UserTest extends ApiTestCase
{
    public function testUsersResponseOk(): void
    {
        $response = $this->makeRequest('GET', '/api/v1/users');

        $this->assertSnapshot($response);
    }

    public function testUsersResponseOkWithDynamicValues(): void
    {
        $response = $this->makeRequestDynamicValues('GET', '/api/v1/users');

        $this->assertSnapshot($response);
    }

    // Response mock for example
    public function makeRequest(string $method, string $uri): array
    {
        return [
            "data" => [
                [
                    "id" => 1,
                    "uuid" => '5d675197-22ec-4338-8647-17b946e9542c',
                    "name" => "Joe Doe",
                    "balance" => 100.00,
                    "createdAt" => "1970-12-30 12:00:00"
                ],
                [
                    "id" => 42,
                    "uuid" => '6d675197-22ec-4338-8647-17b946e9542a',
                    "name" => "Martin Fowler",
                    "balance" => 100.00,
                    "createdAt" => "1980-01-01 00:00:00"
                ],
            ]
        ];
    }

    // Response mock for example
    public function makeRequestDynamicValues(string $method, string $uri): array
    {
        return [
            "data" => [
                [
                    "id" => 52,
                    "uuid" => '7d675197-22ec-4338-8647-17b946e95427',
                    "name" => "Robert Martin",
                    "balance" => 200.01,
                    "createdAt" => "1980-01-01 00:00:00"
                ],
                [
                    "id" => random_int(1, 1000000),
                    "uuid" => '5d675197-22ec-' . random_int(1000, 9999).'-8647-17b946e9542c',
                    "name" => "Joe Doe",
                    "balance" => (float)(random_int(0, 10) . '.' . random_int(0, 99)),
                    "createdAt" => (new DateTime())->format(DATE_ATOM)
                ],
                [
                    "id" => random_int(1, 1000000),
                    "uuid" => '6d675197-22ec-' . random_int(1000, 9999).'-8647-17b946e9542c',
                    "name" => "Martin Fowler",
                    "balance" => (float)(random_int(500, 600) . '.' . random_int(0, 99)),
                    "createdAt" => (new DateTime())->format(DATE_ATOM)
                ],
            ]
        ];
    }

}
