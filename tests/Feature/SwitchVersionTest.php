<?php

namespace Tests\Feature;

use Tests\TestCase;

class SwitchVersionTest extends TestCase
{

    public function testThatWeGetOutputFromDifferentControllersDependingOnVersion()
    {
        $testVersion = function (string $version, array $json) {
            $request = $this->get('/list/default', [
                'Authorization' => 'Bearer test',
                'Accept-Version' => $version,
            ]);
            $request->response
            ->assertStatus(200)
            ->assertJson($json);
        };

        // Confirm that if we switch between the two different versions
        // in the request header we get different output.
        $testVersion('1', [
            'id' => "default",
            'materials' => [],
        ]);

         $testVersion('2', [
            'id' => "default",
            'collections' => [],
         ]);
    }
}
