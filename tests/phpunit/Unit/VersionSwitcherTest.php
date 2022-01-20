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

    /**
     * @dataProvider versionRoutes
     */
    public function testThatMiddlewareSwapsControllerPathDependingOnVersion(string $version, array $expectedRoute): void
    {
        $request = $this->getMockBuilder(Request::class)
            ->onlyMethods(['route'])
            ->getMock();
        $request->headers->set('Accept-Version', $version);

        $request->expects($this->once())
            ->method('route')
            ->will($this->returnValue($this->configuredRoute));

        $middleware = new VersionSwitcher();
        $middleware->handle($request, function ($request) use ($expectedRoute) {
            $manipulatedRoute = $request->getRouteResolver()();
            $this->assertSame($expectedRoute, $manipulatedRoute);
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

    public function versionRoutes(): array
    {
        return [
            // If we are using version 1 we should get the configured route
            // because there is no list controller present in the v1 directory.
            ['1', $this->configuredRoute],
            // If we use version 2 we should get the v2 list controller.
            ['2', [
                1,
                [
                    'uses' => 'App\Http\Controllers\v2\ListController@get',
                    'middleware' => [
                        'auth',
                        'version-switcher',
                    ],
                ],
                [
                    'listId' => 'default',
                ]
            ]],
            // If we are using version 3 we should get the configured route
            // because there is no list controller present in the v1 directory.
            ['3', $this->configuredRoute],
        ];
    }
}
