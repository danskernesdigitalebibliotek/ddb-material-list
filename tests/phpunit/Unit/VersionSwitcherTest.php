<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\Request;
use App\Http\Middleware\VersionSwitcher;
use App\Exceptions\AcceptHeaderWrongFormatException;

class VersionSwitcherTest extends TestCase
{
    // Simulating route defined in the route definition of web.php.
    protected $configuredRoute = [
        1,
        [
            'uses' => 'App\Http\Controllers\ListController@get',
            'middleware' => [
                'auth',
                'version-switcher',
            ],
        ],
        [
            'listId' => 'default',
        ]
    ];
    protected $baseController = 'App\Http\Controllers\ListController';

    /**
     * @dataProvider versionControllerAliases
     */
    public function testThatMiddlewareSwapsControllerPathDependingOnVersion(string $version, string $expectedController): void
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['route'])
            ->getMock();
        $request->headers->set('Accept-Version', $version);

        $request->expects($this->exactly(1))
            ->method('route')
            ->will($this->returnValue($this->configuredRoute));

        $middleware = new VersionSwitcher();
        $middleware->handle($request, function ($request) use ($expectedController) {
            $this->assertSame($expectedController, app()->getAlias($this->baseController));
        });
    }

    public function testThatMiddlewareThrowsExceptionWhenAcceptVersionIsWronglyFormatted(): void
    {
        $this->expectException(AcceptHeaderWrongFormatException::class);
        $this->expectExceptionMessage('The Accept-Version header should be an integer as a string');

        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods([])
            ->getMock();
        $request->headers->set('Accept-Version', 'shouldbeanumber');

        $middleware = new VersionSwitcher();
        $middleware->handle($request, function () {
        //
        });
    }

    public function versionControllerAliases(): array
    {
        return [
            // If we are using version 1 we should get the configured controller
            // because there is no list controller present in the v1 directory.
            ['1', 'App\Http\Controllers\ListController'],
            // If we use version 2 we should get the v2 list controller.
            ['2', 'App\Http\Controllers\v2\ListController'],
            // If we are using version 3 we should get the configured controller
            // because there is no list controller present in the v1 directory.
            ['3', 'App\Http\Controllers\ListController'],
        ];
    }
}
