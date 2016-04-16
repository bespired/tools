<?php

namespace Bespired\Tools;

/**
 * This file is part of the Centagon Build/Selectable package.
 *
 * (c) Centagon <build@centagon.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use Bespired\Tools\Console\Commands\FontelloCommand;
use Bespired\Tools\Console\Commands\RolloutCommand;
use Illuminate\Support\ServiceProvider;

/**
 * Class SelectableServiceProvider
 * @package Build\Selectable
 */
class ToolsServiceProvider extends ServiceProvider
{
   
   
    /**
     * Register the commands.
     * @var array
     */
    protected $commands = [
        FontelloCommand::class,
        RolloutCommand::class,
    ];


    /**
     * Perform post-registration booting of services.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/tools.php' => config_path('tools.php')
        ], 'config');
    }


    /**
     * Register the package.
     */
    public function register()
    {

        $this->commands($this->commands);
        
    }
    
}