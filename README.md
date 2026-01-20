# Material list service for DDB

[![](https://github.com/reload/material-list/workflows/Build,%20test,%20and%20deploy/badge.svg)](https://github.com/reload/material-list/actions?query=workflow%3A%22Build%2C+test%2C+and+deploy%22)
[![](https://github.com/reload/material-list/workflows/Code%20style%20review/badge.svg)](https://github.com/reload/material-list/actions?query=workflow%3A%22Code+style+review%22)
[![codecov](https://codecov.io/gh/reload/material-list/branch/master/graph/badge.svg)](https://codecov.io/gh/reload/material-list)

Material list is a system that stores material identifiers on behalf
of library patrons. This allows patrons to build a check list of
materials they want to remember or are particularily interested in.

Data can be accessed through [a public API documented in OpenAPI 3 format](spec/material-list-1.0.0.yaml).

Access to the API is controlled by [Adgangsplatformen](https://github.com/DBCDK/hejmdal) -
a single sign-on solution for public libraries in Denmark.

## Requirements
Material-list requires a host with the following software:

- Nginx 1.17
- PHP 7.3
- Mysql 5.7+ compatible database

Individual components can be swapped for relevant alternatives. Apache can be used instead of Nginx. Any
[database supported by Laravel](https://laravel.com/docs/5.8/database) such as PostgresSQL, SQLite and SQL Server can
replace MariaDB.

## Usage example

#### Retrieve an access token for the library patron

The access token must be retrieved from Adgangsplatformen by using one of two
types of grant:

1. [Authorization code](https://github.com/DBCDK/hejmdal/blob/master/docs/oauth2.md#11-authentication-code-grant)
2. [Password](https://github.com/DBCDK/hejmdal/blob/master/docs/oauth2.md#12-password-grant)

Usage of Adgangsplatform requires a valid client id and secret which must be
obtained from your library partner or directly from DBC, the company responsible
for running Adgangsplatfomen.

Example for retrieving an access token using password grant:

```
curl -X POST https://login.bib.dk/oauth/token -d 'grant_type=password&password=[patron-password]&username=[patron-username]&agency=[patron-library-agency-id]&client_id=[client-id]&client_secret=[client-secret]'
```

This will return a data structure containing the access token:

```json
{
    "access_token":"abcd1234",
    "token_type":"Bearer",
    "expires_in":2591999
}
```

The access token must be provided as a
[Bearer token in the Authorization header](https://tools.ietf.org/html/rfc6750#section-2.1)
in requests to the Material List API. When accessing the API Material List will
validate tokens by retrieving the corresponding
[user data from Adgangsplaformen](https://github.com/DBCDK/hejmdal/blob/master/docs/oauth2.md#2-get-userinfo).

Token validity can be tested separately by trying to retrieve user data manually:

```
curl https://login.bib.dk/userinfo -H 'Authorization: Bearer [access-token]'
```

If a token cannot be used to retrieve user data this way it cannot be used to access Material List either.

#### Add a material to a list

Materials are added to the list in the form of a PID.

```
curl -X PUT https://test.materiallist.dandigbib.org/list/default/870970-basis:50936155 -H 'Authorization: Bearer abcd1234'
```

Requests should return HTTP response code 201 indicating that the material has
been added to the list.

If the token is not valid then HTTP response code 401 is returned.

#### Retrieve the materials a list

```
curl -X GET https://test.materiallist.dandigbib.org/list/default  -H 'Authorization: Bearer abcd1234'
```

This should return a data structure containing all materials on the
list:

```json
{
    "id": "default",
    "materials": [
        "870970-basis:50936155"
    ]
}
```

## Development prerequisites

In order to run local development you need:

* Docker
* Preferably support for `VIRTUAL_HOST` environment variables for Docker
  containers. Examples: [Dory (OSX)](https://github.com/FreedomBen/dory) or
  [`nginx-proxy`](https://github.com/nginx-proxy/nginx-proxy).

## Other initial steps

If you are using a mac/OSX it is recommended to use nfs on the mounted volumes
in docker-compose.

Copy the docker-compose.mac-nfs.yml:

```sh
$ cp docker-compose.mac-nfs.yml docker-compose.override.yml
```

And follow this [guide](https://github.com/danskernesdigitalebibliotek/dpl-cms/blob/main/mac-nfs.readme.md) in order to set it up.

## Installation

1. Run docker-compose up
2. Copy `.env.example` to `.env` and adjust the configuration.
3. Enter the app container by running: `docker-compose exec app sh`
4. Run `composer install` to install dependencies.
5. Run `./artisan migrate:fresh` to create the database tables.
6. The application is now ready to be tested.
7. The application can be reached on the host at: http://ddb-material-list.docker

### Configuration

The configuration may be passed via environment variables, but the
`.env` file allows for easy configuration of all variables. See
`.env.example` for configuration options.

## Development

### Branching strategy

The project uses the [Git
Flow](https://nvie.com/posts/a-successful-git-branching-model/) model
for branching.

### Continuous integration

GitHub Actions runs tests and checks when new code is pushed.

Pushes to `master` and `develop` deploys the version to the `prod` and
`test` environments respectively. The deploys are also handle by
GitHub Actions.

### Architecture overview

The application code is in the `App` namespace and located in the
`app` directory.

Application bootstrapping is in `bootstrap/app.php`, it sets up the
container, middleware and service providers, and points at the route
file.

Routes are defined in `routes/web.php`. They all point to a method in
a Controller class. See the [Lumen documentation on
routing](https://lumen.laravel.com/docs/routing) for more
information.

### Controllers

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

### Middleware

The application uses middleware from the `oauth2-adgangsplatformen`
package to enforce bearer token authentication for routes.

This ensures that the return value of the `Request::user()` method of
the current request is an instance of an `AdgangsplatformenUser`
object corresponding to the token.

Requests without valid tokens are rejected.

### Error handling

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

### Database

The database schema is defined in `database/migrations`.

See the [Laravel documentation on
migrations](https://laravel.com/docs/migrations) for more
information.

Queries are done with the Laravel query builder. The application does
not use an ORM.

See the [Lumen documentation on
databases](https://lumen.laravel.com/docs/database) for more
information.

### Testing

#### Behavior tests

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

#### API specification lint

To ensure the integrity and quality of the specification we lint it using
[Speccy](https://github.com/wework/speccy).

##### Local

- Install Speccy: `npm install --global speccy`
- Run Speccy: `speccy lint material-list.yaml`

##### Using Docker

Run `docker compose run app speccy lint material-list.yaml`.

#### API specification test

API specification tests are done by generating requests as documented
by the specification and testing if the application reacts as
documented. [Dredd](https://dredd.org/en/latest/) is used for this.

To install Dredd, run: `npm install --global dredd@12`.

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

##### Using Docker

Run `docker compose run app dredd`.

#### Unit tests

Unit tests are primarily used to test parts that are difficult to test
by the previous methods, unexpected exception handling for instance.
Run `./vendor/bin/phpunit` to run the test suite.

## License

Copyright (C) 2019 Danskernes Digitale Bibliotek (DDB)

This project is licensed under the GNU Affero General Public License - see
the [LICENSE.md](LICENSE.md) file for details
