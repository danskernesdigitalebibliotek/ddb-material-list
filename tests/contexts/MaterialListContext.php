<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Behat\Tester\Exception\PendingException;
use Behat\Gherkin\Node\TableNode;
use Carbon\Carbon;
use Faker\Generator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Laravel\Lumen\Testing\Concerns\MakesHttpRequests;

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
     * Scenario state data.
     */
    protected $state = [];

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
        $this->state = [];
        // Boot the app.

        putenv('APP_ENV=testing');
        // Use the test token handler. We can't do OAuth in tests.
        putenv('APP_TOKENCHECKER=test');

        // Use in-memory db for speed.
        putenv('DB_CONNECTION=sqlite');
        putenv('DB_DATABASE=:memory:');

        Facade::clearResolvedInstances();

        $this->app = require __DIR__ . '/../../bootstrap/app.php';

        $url = $this->app->make('config')->get('app.url', env('APP_URL', 'http://localhost'));

        $this->app->make('url')->forceRootUrl($url);

        $this->app->boot();

        // Run migration to create db tables.
        $this->artisan('migrate:fresh');
    }

    /**
     * Call artisan command and return code.
     *
     * (pilfered from Laravel\Lumen\Testing\TestCase)
     *
     * @param string  $command
     * @param array   $parameters
     * @return int
     */
    public function artisan($command, $parameters = [])
    {
        return $this->code = $this->app['Illuminate\Contracts\Console\Kernel']->call($command, $parameters);
    }

    /**
     * Get headers for requests.
     *
     * Most importantly the Authorization header.
     */
    protected function getHeaders() : array
    {
        return [
            'Authorization' => 'Bearer ' . $this->state['token'],
        ];
    }

    /**
     * @Given an unknown user
     */
    public function anUnknownUser()
    {
        // An empty token is considered bad in TestTokenAccess.
        $this->state['token'] = '';
    }

    /**
     * @Given a known user
     * @Given a known user that has no items on list
     */
    public function aKnownUser()
    {
        $this->state['token'] = $this->faker->sha1;
    }

    /**
     * @When fetching the list
     */
    public function fetchingTheList()
    {
        $this->fetchingTheListNamed('default');
    }

    /**
     * @When fetching the :list list
     */
    public function fetchingTheListNamed($list)
    {
        $this->state['list'] = $list;
        $this->get('/list/' . $list, $this->getHeaders());
    }

    /**
     * @Then the system should return success
     */
    public function theSystemShouldReturnSuccess()
    {
        $this->checkStatusCode([200, 201, 204]);
    }

    /**
     * @Then the system should return access denied
     */
    public function theSystemShouldReturnAccessDenied()
    {
        $this->checkStatusCode(401);
    }

    /**
     * @Then the system should return not found
     */
    public function theSystemShouldReturnNotFound()
    {
        $this->checkStatusCode(404);
    }

    /**
     * Check that status code is the expected.
     */
    protected function checkStatusCode($expected)
    {
        if (!is_array($expected)) {
            $expected = [$expected];
        }
        if (!in_array($this->response->getStatusCode(), $expected)) {
            throw new Exception('Status code ' . $this->response->getStatusCode() .
                                ' instead of the expected ' . implode(', ', $expected) .
                                "\nResponse content: \n" . $this->response->getContent());
        }
    }

    /**
     * Test the basic structure of a list response.
     *
     * @return array
     *   The decoded response.
     */
    protected function checkListResponse() : array
    {
        $response = json_decode($this->response->getContent(), true);
        if (empty($response['id'])) {
            throw new Exception('No list id in response');
        }

        if ($response['id'] !== $this->state['list']) {
            throw new Exception('Bad list id in response');
        }

        if (!isset($response['materials'])) {
            throw new Exception('No materials key in response');
        }

        return $response;
    }

    /**
     * @Then the list should be emtpy
     */
    public function theListShouldBeEmtpy()
    {
        $response = $this->checkListResponse();

        if (!empty($response['materials'])) {
            throw new Exception('Material list not empty');
        }
    }

    /**
     * @When :material is added to the list
     */
    public function isAddedToTheList($material)
    {
        $this->put('/list/default/' . $material, [], $this->getHeaders());
    }

    /**
     * @Given they have the following items on the list:
     */
    public function theyHaveTheFollowingItemsOnTheList(TableNode $table)
    {
        foreach ($table as $row) {
            DB::table('materials')->insert([
                'guid' => $this->state['token'],
                'list' => 'default',
                'material' => $row['material'],
                'changed_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
            ]);
        }
    }

    /**
     * @Then the list should contain:
     */
    public function theListShouldContain(TableNode $table)
    {
        $response = $this->checkListResponse();

        $expected = [];
        foreach ($table as $row) {
            $expected[] = $row['material'];
        }

        if ($response['materials'] !== $expected) {
            print_r($expected);
            print_r($response);
            throw new Exception('List content not the expected');
        }
    }

    /**
     * @Then fetching the list should return:
     */
    public function fetchingTheListShouldReturn(TableNode $table)
    {
        $this->fetchingTheList();
        $this->theListShouldContain($table);
    }

    /**
     * @When checking if :material is on the list
     */
    public function checkingIfIsOnTheList($material)
    {
        $this->get('/list/default/' . $material, $this->getHeaders());
    }

    /**
     * @Then :material should be on the list
     */
    public function shouldBeOnTheList($material)
    {
        $this->checkingIfIsOnTheList($material);
        $this->theSystemShouldReturnSuccess();
    }

    /**
     * @When deleting :material from the list
     */
    public function deletingFromTheList($material)
    {
        $this->delete('/list/default/' . $material, [], $this->getHeaders());
    }
}
