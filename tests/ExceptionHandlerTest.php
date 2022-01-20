<?php

namespace Tests;

use Exception;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

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
        $message = 'my content';
        $response = new Response($message, 206);
        $exception = new HttpResponseException($response);
        DB::shouldReceive('table')->andThrow($exception);

        $this->get('/list/default', ['Authorization' => 'Bearer test']);

        $this->assertResponseStatus(206);
        $this->assertEquals($message, $this->response->getContent());
    }
}
