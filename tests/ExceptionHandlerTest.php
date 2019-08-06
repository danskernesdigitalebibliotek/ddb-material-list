<?php

use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Prophecy\Argument;
use Symfony\Component\HttpFoundation\Response;

// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class ExceptionHandlerTest extends TestCase
{
    /**
     * Test that random exceptions results in a 500 response with the
     * "Internal server error." text.
     */
    public function testExceptionHandler()
    {
        DB::shouldReceive('table')->andThrow(new Exception('bad stuff'));

        $this->get('/list/default', ['Authorization' => 'Bearer test']);

        $this->assertResponseStatus(500);
        $this->assertEquals('Internal server error.', $this->response->getContent());
    }

    /**
     * Test that HttpResponseException returns the exceptions response.
     */
    public function testResponseExceptions()
    {
        $response = new Response('my content', 206);
        $exception = new HttpResponseException($response);
        DB::shouldReceive('table')->andThrow($exception);

        $this->get('/list/default', ['Authorization' => 'Bearer test']);

        $this->assertResponseStatus(206);
        $this->assertEquals('my content', $this->response->getContent());
    }
}
