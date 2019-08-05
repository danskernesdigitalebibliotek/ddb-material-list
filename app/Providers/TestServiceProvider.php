<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use League\OAuth2\Client\Provider\AbstractProvider;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Provider\GenericProvider;
use League\OAuth2\Client\Provider\GenericResourceOwner;
use League\OAuth2\Client\Token\AccessToken;

class TestServiceProvider extends ServiceProvider
{

    public function register()
    {
        $this->app->singleton(
            AbstractProvider::class,
            function () {
                // Use an instance of an anonymous class which will return a
                // resource owner with the same id as the provided access token.
                return new class extends GenericProvider {

                    protected function getRequiredOptions()
                    {
                        return [];
                    }

                    public function getResourceOwner(AccessToken $token)
                    {
                        if (empty($token->getToken())) {
                            throw new IdentityProviderException('Unknown user', 404, []);
                        }
                        return new GenericResourceOwner(
                            ['id' => $token->getToken()],
                            'id'
                        );
                    }

                };
            }
        );
    }
}
