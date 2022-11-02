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
     *
     * @var string[]
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
    public function before(BeforeScenarioScope $scope) : void
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
     * @param mixed[] $parameters
     * @return int
     */
    public function artisan($command, $parameters = [])
    {
        /** @var \Illuminate\Contracts\Console\Kernel $consoleKernel */
        $consoleKernel = $this->app['Illuminate\Contracts\Console\Kernel'];
        return $consoleKernel->call($command, $parameters);
    }

    /**
     * Get headers for requests.
     *
     * This includes:
     * - Authorization header to support authentication.
     * - Accept-Version header to toggle between versions.
     *
     * @return mixed[]
     */
    protected function getHeaders(int $version = 1) : array
    {
        return [
            'Authorization' => 'Bearer ' . $this->state['token'],
            'Accept-Version' => $version,
        ];
    }

    /**
     * @Given an unknown user
     */
    public function anUnknownUser() : void
    {
        // An empty token is considered bad in TestTokenAccess.
        $this->state['token'] = '';
    }

    /**
     * @Given a known user
     * @Given a known user that has no items on list
     */
    public function aKnownUser() : void
    {
        $this->state['token'] = $this->faker->sha1;
    }

    /**
     * @When fetching (materials in) the list
     */
    public function fetchingTheList() : void
    {
        $this->fetchingTheListNamed('default');
    }

    /**
     * @When fetching (materials in) the :list list
     *
     * @param string[] $materialIds
     */
    public function fetchingTheListNamed(string $list, array $materialIds = []) : void
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
    public function fetchingCollectionsInTheList() : void
    {
        $this->fetchingCollectionsInTheListNamed('default');
    }

    /**
     * @When fetching collections in the :list list
     *
     * @param string[] $collectionIds
     */
    public function fetchingCollectionsInTheListNamed(string $list, array $collectionIds = []) : void
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
    public function theSystemShouldReturnSuccess() : void
    {
        $this->checkStatusCode([200, 201, 204]);
    }

    /**
     * @Then the system should return access denied
     */
    public function theSystemShouldReturnAccessDenied() : void
    {
        $this->checkStatusCode(401);
    }

    /**
     * @Then the system should return not found
     */
    public function theSystemShouldReturnNotFound() : void
    {
        $this->checkStatusCode(404);
    }

    /**
     * @Then the system should return validation error
     */
    public function theSystemShouldReturnValidationError() : void
    {
        $this->checkStatusCode(422);
    }

    /**
     * Check that status code is the expected.
     *
     * @param int|int[] $expected
     */
    protected function checkStatusCode($expected) :  void
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
     * @return mixed[]
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
        if (!is_array($response)) {
            throw new Exception('Unable to decode response as JSON');
        }
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
     *
     * @param string[] $materials
     */
    public function addMaterialsToList(string $guid, string $list, array $materials) : void
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
    public function theListShouldBeEmpty() : void
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
    public function isAddedToTheList(string $material) : void
    {
        $this->put('/list/default/' . $material, [], $this->getHeaders());
    }

    /**
     * @When collection :collection is added to the list
     */
    public function isCollectionAddedToTheList(string $collection) : void
    {
        $this->put('/list/default/' . $collection, [], $this->getHeaders(2));
    }

    /**
     * @Given they have the following items on the list:
     */
    public function theyHaveTheFollowingItemsOnTheList(TableNode $table) : void
    {
        $materials = $table->getColumn(0);
        // Loose header.
        array_shift($materials);
        $this->addMaterialsToList($this->state['token'], 'default', $materials);
    }

    /**
     * @Then the list should contain (materials):
     */
    public function theListShouldContain(TableNode $table) : void
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
    public function theListShouldContainCollections(TableNode $table) : void
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
    public function fetchingTheListShouldReturn(TableNode $table) : void
    {
        $this->fetchingTheList();
        $this->theListShouldContain($table);
    }

    /**
     * @Then fetching the list should return collections:
     */
    public function fetchingTheListShouldReturnCollections(TableNode $table) : void
    {
        $this->fetchingTheList();
        $this->theListShouldContain($table);
    }

    /**
     * @When checking if (material) :material is on the list
     */
    public function checkingIfIsOnTheList(string $material) : void
    {
        // Sadly there's no $this->head.
        $server = $this->transformHeadersToServerVars($this->getHeaders());

        $this->call('HEAD', '/list/default/' . $material, [], [], [], $server);
    }

    /**
     * @When checking if collection :collection is on the list
     */
    public function checkingIfCollectionIsOnTheList(string $collection) : void
    {
        // Sadly there's no $this->head.
        $server = $this->transformHeadersToServerVars($this->getHeaders(2));

        $this->call('HEAD', '/list/default/' . $collection, [], [], [], $server);
    }

    /**
     * @Then (material) :material should be on the list
     */
    public function shouldBeOnTheList(string $material)  : void
    {
        $this->checkingIfIsOnTheList($material);
        $this->theSystemShouldReturnSuccess();
    }

    /**
     * @When deleting (material) :material from the list
     */
    public function deletingFromTheList(string $material) : void
    {
        $this->delete('/list/default/' . $material, [], $this->getHeaders());
    }

    /**
     * @When deleting collection :collection from the list
     */
    public function deletingCollectionFromTheList(string $collection) : void
    {
        $this->delete('/list/default/' . $collection, [], $this->getHeaders(2));
    }

    /**
     * @When checking if the list contains (materials):
     */
    public function checkingIfTheListContains(TableNode $table) : void
    {
        $materials = $table->getColumn(0);
        array_shift($materials);
        $this->fetchingTheListNamed('default', $materials);
    }

    /**
     * @When checking if the list contains collections:
     */
    public function checkingIfTheListContainsCollections(TableNode $table) : void
    {
        $collections = $table->getColumn(0);
        array_shift($collections);
        $this->fetchingCollectionsInTheListNamed('default', $collections);
    }
}
