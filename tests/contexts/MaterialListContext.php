<?php

use Behat\Behat\Context\Context;
use Behat\Behat\Context\SnippetAcceptingContext;
use Behat\Behat\Hook\Scope\BeforeScenarioScope;
use Behat\Gherkin\Node\TableNode;
use Carbon\Carbon;
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
        putenv('ADGANGSPLATFORMEN_DRIVER=testing');

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
        return $this->app['Illuminate\Contracts\Console\Kernel']->call($command, $parameters);
    }

    /**
     * Get headers for requests.
     *
     * This includes:
     * - Authorization header to support authentication.
     * - Accept-Version header to toggle between versions.
     */
    protected function getHeaders($version = 1) : array
    {
        return [
            'Authorization' => 'Bearer ' . $this->state['token'],
            'Accept-Version' => $version,
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
     * @When fetching (materials in) the list
     */
    public function fetchingTheList()
    {
        $this->fetchingTheListNamed('default');
    }

    /**
     * @When fetching (materials in) the :list list
     */
    public function fetchingTheListNamed($list, $materialIds = [])
    {
        $query = '';
        if (!empty($materialIds)) {
            $query = "?material_ids=" . implode(',', $materialIds);
        }
        $this->state['list'] = $list;
        $this->get('/list/' . $list . $query, $this->getHeaders());
    }

    /**
     * @When fetching collections in the list
     */
    public function fetchingCollectionsInTheList()
    {
        $this->fetchingCollectionsInTheListNamed('default');
    }

    /**
     * @When fetching collections in the :list list
     */
    public function fetchingCollectionsInTheListNamed($list, $collectionIds = [])
    {
        $query = '';
        if (!empty($collectionIds)) {
            $query = "?collection_ids=" . implode(',', $collectionIds);
        }
        $this->state['list'] = $list;
        $this->get('/list/' . $list . $query, $this->getHeaders(2));
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
     * @Then the system should return validation error
     */
    public function theSystemShouldReturnValidationError()
    {
        $this->checkStatusCode(422);
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
        $this->checkStatusCode(200);
        $json = $this->response->getContent();
        if (!$json) {
            throw new Exception('Empty response');
        }
        $response = json_decode($json, true);
        if (empty($response['id'])) {
            throw new Exception('No list id in response');
        }

        if ($response['id'] !== $this->state['list']) {
            throw new Exception('Bad list id in response');
        }

        if (!isset($response['materials']) && !isset($response['collections'])) {
            throw new Exception('No materials or collections key in response');
        }

        return $response;
    }

    /**
     * Add materials to list.
     */
    public function addMaterialsToList(string $guid, string $list, array $materials)
    {
        foreach ($materials as $material) {
            DB::table('materials')->insert([
                'guid' => $guid,
                'list' => $list,
                'material' => $material,
                'changed_at' => Carbon::now()->format('Y-m-d H:i:s.u'),
            ]);
        }
    }

    /**
     * @Then the list should be empty
     */
    public function theListShouldBeEmpty()
    {
        $response = $this->checkListResponse();

        if (isset($response['materials']) && !empty($response['materials'])) {
            throw new Exception('Material list not empty');
        }


        if (isset($response['materials']) && !empty($response['materials'])) {
            throw new Exception('Material list not empty');
        }
    }

    /**
     * @When (material) :material is added to the list
     */
    public function isAddedToTheList($material)
    {
        $this->put('/list/default/' . $material, [], $this->getHeaders());
    }

    /**
     * @When collection :collection is added to the list
     */
    public function isCollectionAddedToTheList($collection)
    {
        $this->put('/list/default/' . $collection, [], $this->getHeaders(2));
    }

    /**
     * @Given they have the following items on the list:
     */
    public function theyHaveTheFollowingItemsOnTheList(TableNode $table)
    {
        $materials = $table->getColumn(0);
        // Loose header.
        array_shift($materials);
        $this->addMaterialsToList($this->state['token'], 'default', $materials);
    }

    /**
     * @Then the list should contain (materials):
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
     * @Then the list should contain collections:
     */
    public function theListShouldContainCollections(TableNode $table)
    {
        $response = $this->checkListResponse();

        $expected = [];
        foreach ($table as $row) {
            $expected[] = $row['collection'];
        }

        if ($response['collections'] !== $expected) {
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
     * @Then fetching the list should return collections:
     */
    public function fetchingTheListShouldReturnCollections(TableNode $table)
    {
        $this->fetchingTheList();
        $this->theListShouldContain($table);
    }

    /**
     * @When checking if (material) :material is on the list
     */
    public function checkingIfIsOnTheList($material)
    {
        // Sadly there's no $this->head.
        $server = $this->transformHeadersToServerVars($this->getHeaders());

        $this->call('HEAD', '/list/default/' . $material, [], [], [], $server);
    }

    /**
     * @When checking if collection :collection is on the list
     */
    public function checkingIfCollectionIsOnTheList($collection)
    {
        // Sadly there's no $this->head.
        $server = $this->transformHeadersToServerVars($this->getHeaders(2));

        $this->call('HEAD', '/list/default/' . $collection, [], [], [], $server);
    }

    /**
     * @Then (material) :material should be on the list
     */
    public function shouldBeOnTheList($material)
    {
        $this->checkingIfIsOnTheList($material);
        $this->theSystemShouldReturnSuccess();
    }

    /**
     * @When deleting (material) :material from the list
     */
    public function deletingFromTheList($material)
    {
        $this->delete('/list/default/' . $material, [], $this->getHeaders());
    }

    /**
     * @When deleting collection :collection from the list
     */
    public function deletingCollectionFromTheList($collection)
    {
        $this->delete('/list/default/' . $collection, [], $this->getHeaders(2));
    }

    /**
     * @When checking if the list contains (materials):
     */
    public function checkingIfTheListContains(TableNode $table)
    {
        $materials = $table->getColumn(0);
        array_shift($materials);
        $this->fetchingTheListNamed('default', $materials);
    }

    /**
     * @When checking if the list contains collections:
     */
    public function checkingIfTheListContainsCollections(TableNode $table)
    {
        $collections = $table->getColumn(0);
        array_shift($collections);
        $this->fetchingCollectionsInTheListNamed('default', $collections);
    }
}
