<?php

namespace bfinlay\SpreadsheetSeeder;

use bfinlay\SpreadsheetSeeder\Console\SeedCommand;
use bfinlay\SpreadsheetSeeder\Support\StrMacros;
use bfinlay\SpreadsheetSeeder\Support\Workaround\RefreshDatabase\RefreshDatabaseMySqlConnection;
use Composer\Semver\Semver;
use Illuminate\Database\Connection;
use Illuminate\Database\MySqlConnection;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

class SpreadsheetSeederServiceProvider extends ServiceProvider
{
    /**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
    protected $defer = false;

    /**
     * All of the container bindings that should be registered.
     *
     * @var array
     */
    public $bindings = [
    ];

    /**
     * All of the container singletons that should be registered.
     *
     * @var array
     */
    public $singletons = [
        SpreadsheetSeederSettings::class => SpreadsheetSeederSettings::class,
    ];

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(SeedCommand::class, function ($app) {
            return new SeedCommand($app['db']);
        });

        StrMacros::registerSupportMacros();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->bindGrammarClasses();
        $this->commands([
            SeedCommand::class,
        ]);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return array();
    }

    protected function getMySqlConnectionClass()
    {
        $phpversion = explode("-", phpversion());
        if (
            app()->runningUnitTests() &&
            Semver::satisfies(app()->version(), "^6.0|^7.0|^8.0") &&
            Semver::satisfies($phpversion[0], "^8.0")
        )
            return RefreshDatabaseMySqlConnection::class;

        return MySqlConnection::class;
    }

    protected function bindGrammarClasses()
    {
        $connections = [
            'mysql' => [
                'connection' => $this->getMySqlConnectionClass(),
                'schemaGrammar' => MySqlGrammar::class,
            ],
        ];

        foreach($connections as $driver => $class) {
            Connection::resolverFor($driver, function($pdo, $database = '', $tablePrefix = '', array $config = []) use ($driver, $class) {
                $connection = new $class['connection']($pdo, $database, $tablePrefix, $config);
                
                // In Laravel versions 11 and below, Illuminate\Database\Grammar does not expect any arguments
                // in the constructor. In Laravel 12, it does. We need to check the version and pass the arguments
                // if the version is 12 or above.

                if (Semver::satisfies(app()->version(), '<12.0')) {
                    $connection->setSchemaGrammar(new $class['schemaGrammar']());
                } else {
                    $connection->setSchemaGrammar(new $class['schemaGrammar']($connection));
                }

                return $connection;
            });
        }
    }
}