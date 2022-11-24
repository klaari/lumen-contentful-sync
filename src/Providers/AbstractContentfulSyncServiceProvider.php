<?php

namespace Digia\Lumen\ContentfulSync\Providers;

use Digia\Lumen\ContentfulSync\Console\Commands\SyncAssetsCommand;
use Digia\Lumen\ContentfulSync\Console\Commands\SyncContentsCommand;
use Digia\Lumen\ContentfulSync\Contracts\ContentfulSyncServiceContract;
use Digia\Lumen\ContentfulSync\Http\Middleware\WebhookAuthenticationMiddleware;
use Illuminate\Support\ServiceProvider;
use Jalle19\Laravel\LostInterfaces\Providers\ServiceProvider as ServiceProviderInterface;
use Laravel\Lumen\Application;
use Nord\Lumen\Contentful\ContentfulServiceContract;

/**
 * Class AbstractContentfulSyncServiceProvider
 * @package Digia\Lumen\ContentfulSync\Providers
 */
abstract class AbstractContentfulSyncServiceProvider extends ServiceProvider implements ServiceProviderInterface
{

    /**
     * @param Application $app
     */
    abstract protected function registerContentfulSyncServiceBindings(Application $app): void;

    /**
     * @inheritDoc
     */
    public function register(): void
    {
        app()->configure('contentfulSync');

        // Configure how ContentfulSyncServiceContract should be built
        $this->registerContentfulSyncServiceBindings(app());

        // Configure how commands should be built
        app()->bind(SyncAssetsCommand::class, function (Application $app) {
            return new SyncAssetsCommand(config('contentfulSync.content_types'),
                $app->make(ContentfulServiceContract::class),
                $app->make(ContentfulSyncServiceContract::class));
        });

        app()->bind(SyncContentsCommand::class, function (Application $app) {
            return new SyncContentsCommand(config('contentfulSync.content_types'),
                $app->make(ContentfulServiceContract::class),
                $app->make(ContentfulSyncServiceContract::class));
        });

        // Configure how middleware should be built
        app()->bind(WebhookAuthenticationMiddleware::class, function () {
            $expectedUsername = config('contentfulSync.webhookUsername');
            $expectedPassword = config('contentfulSync.webhookPassword');

            return new WebhookAuthenticationMiddleware($expectedUsername, $expectedPassword);
        });
    }

    /**
     * @inheritDoc
     */
    public function boot(): void
    {
    }
}
