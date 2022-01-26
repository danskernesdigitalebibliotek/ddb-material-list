<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use App\Http\Middleware\VersionSwitcher;
use App\Exceptions\AcceptHeaderWrongFormatException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class VersionSwitcherTest extends TestCase
{
    // Simulating route defined in the route definition of web.php.
    protected $configuredRoute = [
        1,
        [
            'uses' => 'App\Http\Controllers\v%version%\ListController@get',
            'middleware' => [
                'auth',
                'version-switcher',
            ],
        ],
        [
            'listId' => 'default',
        ]
    ];
    protected $baseController = 'App\Http\Controllers\v%version%\ListController';

    /**
     * @dataProvider versionControllerAliases
     */
    public function testThatMiddlewareSwapsControllerPathDependingOnVersion(
        string $version,
        string $expectedController
    ): void {
        Config::set('api.version', '1');

        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['route'])
            ->getMock();
        $request->headers->set('Accept-Version', $version);

        $request->expects($this->exactly(1))
            ->method('route')
            ->will($this->returnValue($this->configuredRoute));

        // The v1000 controller does not exist. We expect a not found exception to be thrown in that case.
        if ($version == '1000') {
            $this->expectException(NotFoundHttpException::class);
        }
        $middleware = new VersionSwitcher();

        $middleware->handle($request, function ($request) use ($version, $expectedController) {
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
            // // because there is no list controller present in the v1 directory.
            ['1', 'App\Http\Controllers\v1\ListController'],
            // If we use version 2 we should get the v2 list controller.
            ['2', 'App\Http\Controllers\v2\ListController'],
            // If we are using version 1000 we should get an exception
            // because there is no list controller present in the v1000 namesapce.
            ['1000', ''],
        ];
    }
}
