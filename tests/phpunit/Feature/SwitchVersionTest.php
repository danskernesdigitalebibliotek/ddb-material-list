<?php

namespace Tests\Feature;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Tests\TestCase;

class SwitchVersionTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @dataProvider versionResponses
     */
    public function testThatWeGetOutputFromDifferentControllersDependingOnVersion(string $version, array $jsonResponse)
    {
        // Confirm that if we switch between different versions
        // in the request header we get different output.
        $request = $this->get('/list/default', [
            'Authorization' => 'Bearer test',
            'Accept-Version' => $version,
        ]);
        $request->response
        ->assertStatus(200)
        ->assertJson($jsonResponse);
    }

    public function versionResponses(): array
    {
        return [
            ['1', [
                'id' => "default",
                'materials' => [],
            ]],
            ['2', [
                'id' => "default",
                'collections' => [],
            ]],
        ];
    }
}
