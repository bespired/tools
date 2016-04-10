<?php

namespace Bespired\Tools\Console\Commands;

use Build\Foundation\ServiceProvider;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class RolloutCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'tools:rollout:migrations';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Rollout Migration files.';

	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$this->info('Hello Migration.');
		
		$packages= config('build.universe.packages');
		if ( count($packages) == 0 )
		{
			$this->error('No packages found in unverse config.');
			return;
		}

		$paths = ServiceProvider::pathsToPublish();

		foreach ($paths as $sourceDir => $destinationDir) {
			if ( substr($destinationDir, -10) == 'migrations' )
			{
				if( File::copyDirectory($sourceDir, $destinationDir) )
				{
					$this->info("copied {$sourceDir}");
				}
			}
		}

		$this->info('Done.');

	}


}
