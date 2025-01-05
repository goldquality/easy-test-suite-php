# PHPUnit Snapshots

**Snapshot testing** is a testing technique that captures the output of a function/component/endpoint and saves it as a reference.  

On subsequent test runs, the output is compared against the saved reference to ensure consistency and catch unexpected changes.

## Benefits

- **Speed:** Quickly create tests without manually managing output assertions.
- **Flexibility:** Combine with another asserts, like `assertDatabaseHas`, `assertQueue`, `assertCookie` and others.
- **Consistency:** Automatically verifies outputs against previously captured snapshots, reducing human error.
- **Framework Agnostic:** Compatible with popular frameworks like Symfony, Laravel, Yii. Making it versatile for any project setup.
___

## Comparison with competitors

phpunit-snapshots vs [spatie/phpunit-snapshot-assertions](https://github.com/spatie/phpunit-snapshot-assertions):
- phpunit-snapshots provides a robust array of masks for dynamic values, which allows for a wide set of patterns.
- phpunit-snapshots places a protection line ("DELETE THIS ROW") in newly created snapshots to prompt user review and manual editing, which encourages careful validation of the snapshot’s correctness.
___

## Installation

```bash
composer require --dev goldquality/phpunit-snapshots
```

## Configuration
1. Create or add to existing BaseTestCase e.g. `ApiTestCase`
```php
<?php

namespace Tests; // laravel
//namespace App\Tests; // symfony

use GoldQuality\PHPUnitSnapshots\SnapshotAssertTrait;
use Illuminate\Testing\TestResponse;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;


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
```
[see example implementation](/tests)

## Usage
1. **Extend TestCase**: Extend the `ApiTestCase` .
2. **Invoke assert method**: Use the `assertSnapshot()` or `assertResponseSnapshot()` method in your test cases with the response content as an argument.
3. **Run your tests**. On the initial test run, a snapshot file is created with the JSON response content.  

Example of a generated snapshot file `test_response_ok_0.json`:
```JSON
⚠️DELETE THIS ROW ⚠️
{
  "data": [
    {
      "id": 1,
      "name": "Joe Doe",
      "balance": 100.00,
      "createdAt": "1970-12-30 12:07:24"
    },
    {
      "id": "@integer@", # use of masks for dynamic values
      "name": "Martin Fowler",
      "balance": "@double@.greaterThan(10).lowerThan(50.12)", # mask conditions
      "createdAt": "@datetime@"
    }
  ]
}
```
4. **Edit Snapshot**: Open the created file and remove the protective line "DELETE THIS ROW" at the top.
5. **Masks (Optional)**: Add [masks](#available-masks) to manage auto-generated values. 
6. **Re-run Tests**: Verify that the response contents match your established snapshots.

This method allows developers to ensure their application's output remains consistent across updates and refactoring, enhancing test coverage and reliability with minimal effort.
___
## Examples

**Symfony Example:**
```php
namespace App\Tests\Controller;

use App\Tests\ApiTestCase;

class ExampleTest extends ApiTestCase
{
    public function test_response_ok(): void
    {
        // Simulate a logged-in user
        $client = static::createClient();
        $client->loginUser($this->getTestUser()); // Replace with your method to fetch a test user
        
        // Make a JSON request
        $response = $client->request('GET', '/api/v1/users', [], [], [
            'HTTP_ACCEPT' => 'application/json',
        ]);

        // Assert the response snapshot
        $this->assertSnapshot($response->toArray()); // Asserts that response content matches the test_response_ok_0.json
        // or $this->assertResponseSnapshot($response);
        
        // Additional assertions
        $this->assertEquals(200, $response->getStatusCode());
    }
}
```

**Laravel Example:**
```php
namespace Tests\Feature;

use Tests\ApiTestCase;

class ExampleTest extends ApiTestCase
{
    public function test_response_ok(): void
    {
        // Simulate a logged-in user
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Make a JSON request
        $response = $this->getJson('api/v1/users');

        // Assert the response snapshot
        $this->assertSnapshot($response->collect()->toArray());   // Asserts that response content matches the test_response_ok_0.json
        // or $this->assertResponseSnapshot($response);
        
        // Additional assertions
        $response->assertStatus(200);
    }
}
```

## Available masks
- `@string@`
- `@integer@`
- `@number@`
- `@double@`
- `@boolean@`
- `@time@`
- `@date@`
- `@datetime@`
- `@timezone@` || `@tz`
- `@array@`
- `@array_previous@` - match next array element using pattern from previous element
- `@array_previous_repeat@` - match all remaining array elements using pattern from previous element
- `@...@` - unbounded array, once used matcher will skip any further array elements
- `@null@`
- `@*@` || `@wildcard@`
- `@uuid@`
- `@ulid@`
- `@json@`
- `@string@||@integer@` - string OR integer
- For more patterns, see [php-matcher available patterns](https://github.com/coduo/php-matcher#available-patterns).

___

## Advanced configuration

If you need more control you can extend `SnapshotHandler` and implement as you need.
```php
protected function initSnapshotHandler(): SnapshotHandler 
{
   // Custom initialization logic
}
```
___

## Testing
```composer test```

___
## FAQ

### How do snapshot files work?

The first time a test using `assertSnapshot()` is run, a snapshot file (in JSON format) is created, capturing the component's or function's output. In subsequent runs, the output is compared against this file. If the outputs differ, the test will fail, alerting you to unintended changes.

### My test fails due to a mismatch with the snapshot; what now?

If a test fails because the output does not match the snapshot, you should:
1. Review the differences to determine if they are expected changes.
2. If the changes are intended, manually edit the snapshot file or delete snapshot file and re-run the test to generate a new snapshot.
3. If the changes are unintended, investigate and fix the underlying issue in your application.

### What are masks, and how do I use them?

Masks are patterns used in snapshot files to handle dynamic values (e.g., timestamps, IDs) that might change between test runs. For example, `@integer@` can be used for dynamic integer values. After the first test run, you can edit the snapshot to include these masks.

### Why do I need to delete the "DELETE THIS ROW" line in the snapshot?

The "DELETE THIS ROW" line serves as a protective line to remind developers to review and edit the snapshot file after its initial creation. You should remove this line to finalize the snapshot structure.

### Can I customize the naming of snapshot files?

Currently, the naming of snapshot files automatically based on your test case names.
