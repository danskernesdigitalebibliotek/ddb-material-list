<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Illuminate\Support\Facades\Facade;
use Laravel\Lumen\Testing\Concerns\MakesHttpRequests;

use Faker\Generator;


// phpcs:ignore PSR1.Classes.ClassDeclaration.MissingNamespace
class MaterialListContext implements Context, SnippetAcceptingContext
{
    use MakesHttpRequests;

    /**
     * The application instance.
     *
     * @var \Laravel\Lumen\Application
     */
    protected $app;

    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Faker instance.
     *
     * @var Faker\Generator
     */
    protected $faker;

    /**
     * The token to use in requests.
     */
    protected $token = '';

    public function __construct()
    {
        $this->faker = Faker\Factory::create();
    }

    /**
     * Boot the app before each scenario.
     *
     * @BeforeScenario
     */
    public function before(BeforeScenarioScope $scope)
    {
        $this->token = '';
        // Boot the app.
        putenv('APP_ENV=testing');

        Facade::clearResolvedInstances();

        $this->app = require __DIR__ . '/../../bootstrap/app.php';

        $url = $this->app->make('config')->get('app.url', env('APP_URL', 'http://localhost'));

        $this->app->make('url')->forceRootUrl($url);

        $this->app->boot();
    }

    /**
     * Get headers for requests.
     *
     * Most importantly the Authorization header.
     */
    protected function getHeaders() : array
    {
        return [
            'Authorization' => 'Bearer ' . $this->token,
        ];
    }

    /**
     * @Given an unknown user
     */
    public function anUnknownUser()
    {
        // An empty token is considered bad in TestTokenAccess.
        $this->token = '';
    }

    /**
     * @Given a known user
     */
    public function aKnownUser()
    {
        $this->token = $this->faker->sha1;
    }

    /**
     * @When fetching the list
     */
    public function fetchingTheList()
    {
        $this->get('/list/default', $this->getHeaders());
    }

    /**
     * @Then the system should return access denied
     */
    public function theSystemShouldReturnAccessDenied()
    {
        if ($this->response->getStatusCode() != 401) {
            throw new Exception('Status code ' . $this->response->getStatusCode() . ' instead of the expected 401');
        }
    }

    /**
     * @Then the system should return success
     */
    public function theSystemShouldReturnSuccess()
    {
        if ($this->response->getStatusCode() != 200) {
            throw new Exception('Status code ' . $this->response->getStatusCode() . ' instead of the expected 200');
        }
    }
}
