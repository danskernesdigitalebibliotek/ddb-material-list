[![](https://img.shields.io/codecov/c/github/reload/material-list.svg?style=for-the-badge)](https://codecov.io/gh/reload/material-list)

## Installation ##

1. Run `composer install` to install dependencies.
2. Copy `.env.example` to `.env` and adjust the configuration.
3. Run `./artisan migrate:fresh` to create the database tables.
4. Serve using `php -S 0.0.0.0:8000 -t public/` (for testing), FPM, or
   Apache.

### Configuration ###

The configuration may be passed via environment variables, but the
`.env` file allows for easy configuration of all variables. See
`.env.example` for configuration options.

## Development ##

### Branching strategy ###

The project uses the [Git
Flow](https://nvie.com/posts/a-successful-git-branching-model/) model
for branching.

### Continuous integration ###

GitHub Actions runs tests and checks when new code is pushed.

Pushes to `master` and `develop` deploys the version to the `prod` and
`test` environments respectively. The deploys are also handle by
GitHub Actions.

### Achitecture overview ###

The application code is in the `App` namespace and located in the
`app` directory.

Application bootstrapping is in `bootstrap/app.php`, it sets up the
container, middleware and service providers, and points at the route
file.

Routes are defined in `routes/web.php`. They all point to a method in
a Controller class. See the [Lumen documentation on
routing](https://lumen.laravel.com/docs/routing) for more
information.

### Controllers ###

The controller classes is defined in `App\Http\Controllers`. The
controller methods handling requests gets the URL path placeholders as
arguments, and typehinted arguments are auto-wired from the container.
They can return array data (which is automatically transformed into a
JSON response), a `Illuminate\Http\Response` (which subclasses
`Symfony\Component\HttpFoundation\Response`), or throw an exception
(which is converted to an appropriate response by the error handler).

See the [Lumen documentation on
controllers](https://lumen.laravel.com/docs/controllers) for more
information.

### Middleware ###

The application uses middleware from the `oauth2-adgangsplatformen` 
package to enforce bearer token authentication for routes.

This ensures that the return value of the `Request::user()` method of 
the current request is an instance of an `AdgangsplatformenUser` 
object corresponding to the token.

Requests without valid tokens are rejected.

### Error handling ###

The `App\Exceptions\Handler` handles exceptions thrown by the
controllers. It converts
`Symfony\Component\HttpKernel\Exception\HttpException` and its
subclasses into the corresponding responses (`NotFoundHttpException`
into a 404, for instance). For
`Illuminate\Http\Exceptions\HttpResponseException` (which is an
exception that encapsulates a `Response`) it simply uses the
exceptions response. Everything else causes a "500 Internal error"
response, unless the `APP_DEBUG` environment variable is true, in
which case it serves the exception message as `text/plain` to ease
debugging.

### Database ###

The database schema is defined in `database/migrations`.

See the [Laravel documentation on
migrations](https://laravel.com/docs/migrations) for more
information.

Queries are done with the Laravel query builder. The application does
not use an ORM.

See the [Lumen documentation on
databases](https://lumen.laravel.com/docs/database) for more
information.

### Testing ###

#### Behavior tests ####

Most tests are done as behavior test using Behat. The features are in
`tests/features` while the context classes reside in `tests/contexts`,
and the tests can be run with `./vendor/bin/behat`.

The context doesn't interact with the application over HTTP, rather
the application is booted inside the test for each scenario. This is
the same way that unit tests of controllers is done, in fact the
context is using the same
`Laravel\Lumen\Testing\Concerns\MakesHttpRequests` trait that
`Laravel\Lumen\Testing\TestCase` uses to construct the right request
objects.

This also makes code coverage collection simpler. Behat writes
coverage to `coverage`, which can be rendered to HTML with
`./vendor/bin/phpcov merge --html=./coverage/html ./coverage`.

#### API specification lint ####

To ensure the integrity and quality of the specification we lint it using
[Speccy](https://github.com/wework/speccy).

To install Speccy, run `npm install --global speccy`

To run Speccy, run `speccy lint material-list.yaml`

#### API specification test ####

API specification tests are done by generating requests as documented
by the specification and testing if the application reacts as
documented. [Dredd](https://dredd.org/en/latest/) is used for this.

To install Dredd, run: `npm install --global dredd`.

Running Dredd is as simple as `dredd`. Dredd is configured to run
`php -S 0.0.0.0:8080 -t public` to start the server, which simply runs the
application using the PHP built-in webserver.

In order to ensure the right conditions for each test, Dredd uses a
hooks file (`tests/dredd/hooks.php`), which allows for setting
fixtures or modifying the requests/response.

To get the names of requests (for use in hook file), use `dredd
--names`. Getting dredd to display any output from the hook file (for
debugging), you need to run it in verbose mode: `dredd
--loglevel=debug`.

#### Unit tests ####

Unit tests are primarily used to test parts that are difficult to test
by the previous methods, unexpected exception handling for instance.
Run `./vendor/bin/phpunit` to run the test suite.

## Hosting ##
The project ships with an full hosting-setup based on Google Kubernetes Engine. See the [infrastructure documentation](infrastructure/README.md)

## License

Copyright (C) 2019 Danskernes Digitale Bibliotek (DDB)

This project is licensed under the GNU Affero General Public License - see 
the [LICENSE.md](LICENSE.md) file for details
