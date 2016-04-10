<?php

namespace Bespired\Tools\Seeds;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ToolsSeedSeeder extends Seeder
{
  
	public function run()
	{
	   
		// get files in database/seeds folder
		$path= base_path('database/seeds');
		$files = array_map(function($filename){
			return str_replace( ".php", "", $filename );
		}, 
			array_map('basename', File::files($path))
		);

		// get entries in database
		$seeded = DB::table('seeded')->lists('seeder');


		// find seeds that need to be done
		foreach ($files as $file) {
			
			if ($file == 'DatabaseSeeder')
				continue;

			if (in_array($file, $seeded))
				continue;

			// call the seeder
			Model::unguard();

			$this->command->info("Calling $file");
			try{

				$this->call( $file );
				DB::table('seeded')->insert(['seeder' => $file]);

			}catch (Exception $e) {
				$this->command->error("Error in $file");
			}
			
			Model::reguard();

			
		}

	}



}
