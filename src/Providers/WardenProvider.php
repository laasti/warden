<?php

namespace Laasti\Warden\Providers;

class WardenProvider extends \League\Container\ServiceProvider\AbstractServiceProvider
{

    protected $provides = [
        'Laasti\Warden\Warden',
        'Laasti\Warden\Repositories\RepositoryInterface',
        'Laasti\Warden\Sessions\SessionInterface',
        'Laasti\Warden\Hashers\HasherInterface',
        'Laasti\Warden\Sessions\NativeSession',
        'Laasti\Warden\Hashers\NativeHasher',
    ];

    protected $defaultConfig = [
        'repository' => 'Laasti\Warden\Repositories\PdoUserRepository',
        'session' => 'Laasti\Warden\Sessions\NativeSession',
        'hasher' => 'Laasti\Warden\Hashers\NativeHasher',
    ];

    public function register()
    {
        $globalConfig = $this->getContainer()->get('config');
        $config = isset($globalConfig['warden']) ? $globalConfig['warden'] : [];
        $config += $this->defaultConfig;
        $di = $this->getContainer();
        $di->add('Laasti\Warden\Sessions\NativeSession');
        $di->add('Laasti\Warden\Hashers\NativeHasher');
        $this->getContainer()->add('Laasti\Warden\Repositories\RepositoryInterface', function () use ($di, $config) {
            return $di->get($config['repository']);
        });
        $this->getContainer()->add('Laasti\Warden\Sessions\SessionInterface', function () use ($di, $config) {
            return $di->get($config['session']);
        });
        $this->getContainer()->add('Laasti\Warden\Hashers\HasherInterface', function () use ($di, $config) {
            return $di->get($config['hasher']);
        });

        $this->getContainer()->share('Laasti\Warden\Warden')->withArguments([
            'Laasti\Warden\Repositories\RepositoryInterface',
            'Laasti\Warden\Sessions\SessionInterface',
            'Laasti\Warden\Hashers\HasherInterface',
        ]);
    }
}
