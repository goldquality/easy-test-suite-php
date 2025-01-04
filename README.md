# Easy Test Suite

___
## Benefits

- **Speed:** Quickly create tests without manually managing output assertions.
- **Flexibility:** Combine with another asserts, like `assertDatabaseHas`, `assertQueue`, `assertCookie` and others.
- **Consistency:** Automatically verifies outputs against previously captured snapshots, reducing human error.
- **Framework Agnostic:** Compatible with popular frameworks like Symfony, Laravel, Yii. Making it versatile for any project setup.

Snapshot testing allows developers to ensure their application's output remains consistent across updates and refactoring, enhancing test coverage and reliability with minimal effort.

___

The `SnapshotAssertTrait` provides the `assertSnapshot()` method:
- Automatically create a JSON file with the response content during the first test run.
- Compare the response against the saved snapshot in subsequent test runs.


## Installation

```bash
composer require --dev goldquality/easy-test-suite-php
```


## Usage

1. **Integrate trait**: Add the `\GoldQuality\EasyTestSuite\SnapshotAssertTrait` to your test class.
2. **Invoke assert method**: Use the `assertSnapshot()` method in your test cases with the response content as an argument.
3. **Run your tests**. On the initial test run, a snapshot file is created with the JSON response content.  

Example of a generated snapshot file `test_response_ok_0.json`:
```JSON
⚠️DELETE THIS ROW ⚠️
{
  "data": [
    {
      "id": 1,
      "name": "Joe Doe",
      "createdAt": "1970-12-30 12:07:24"
    },
    {
      "id": "@integer@", #use of masks for dynamic values
      "name": "Joe Doe",
      "createdAt": "@datetime@"
    }
  ]
}
```
4. **Edit Snapshot**: Open the created file and remove the protective line "DELETE THIS ROW" at the top.
5. **Masks (Optional)**: Add [masks](#available-masks) to manage auto-generated values. 
6. **Re-run Tests**: Verify that the response contents match your established snapshots.
7. **Verify and Done**: Your setup should now validate changes against snapshots.

This method allows developers to ensure their application's output remains consistent across updates and refactoring, enhancing test coverage and reliability with minimal effort.
___
## Examples

**Symfony Example:**
```php
namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use GoldQuality\EasyTestSuite\SnapshotAssertTrait;

class ExampleTest extends KernelTestCase
{
    use SnapshotAssertTrait;

    public function testResponseOk(): void
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
        
        // Additional assertions
        $this->assertEquals(200, $response->getStatusCode());
    }
}
```

**Laravel Example:**
```php
namespace Tests\Feature;

use Tests\TestCase;
use GoldQuality\EasyTestSuite\SnapshotAssertTrait;

class ExampleTest extends TestCase
{
    use SnapshotAssertTrait;

    public function test_response_ok(): void
    {
        // Simulate a logged-in user
        $user = User::factory()->create();
        $this->actingAs($user);
        
        // Make a JSON request
        $response = $this->getJson('api/v1/users');

        // Assert the response snapshot
        $this->assertSnapshot($response->collect()->toArray());   // Asserts that response content matches the test_response_ok_0.json
        
        // Additional assertions
        $response->assertStatus(200);
    }
}
```

## Available masks
- @string@
- @integer@
- @number@
- @double@
- @boolean@
- @time@
- @date@
- @datetime@
- @timezone@ || @tz
- @array@
- @array_previous@ - match next array element using pattern from previous element
- @array_previous_repeat@ - match all remaining array elements using pattern from previous element
- @...@ - unbounded array, once used matcher will skip any further array elements
- @null@
- @*@ || @wildcard@
- @uuid@
- @ulid@
- @json@
- @string@||@integer@ - string OR integer
- For more patterns, see [php-matcher available patterns](https://github.com/coduo/php-matcher#available-patterns).
