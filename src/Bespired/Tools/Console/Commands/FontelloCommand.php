<?php

namespace Bespired\Tools\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class FontelloCommand extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'tools:fontello';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Unpack Fontello files.';

	
	protected $download_path; 
	protected $resources_path; 
	protected $zip_path; 
	protected $font_path; 
	protected $fontello;
	protected $fontname;

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$this->info('Hello Fontello.');
		$this->download_path  = config('tools.download_path');
		$this->resources_path = config('tools.resources_path');

		if (!count($this->fontellozips())){
			$this->info('No fontello found in your download folder.');
			return;
		}
		
		$this->getlatest();
		$this->info("{$this->fontello} is the latest fontello in your download folder.");

		if ( !$this->unzipper() )
		{
			$this->error('Unzipping went wrong.');
			return;
		}

		$this->getfontname();
		$this->info("{$this->fontname} is the fontname.");

		$this->create_scss();

		$this->copy_zip();


		$this->info('Done.');

	}



	private function create_scss()
	{
		$src= $this->zip_path . '/css/' .  $this->fontname . '.css';
		$dst= $this->resources_path . 'sass/' .  $this->fontname . '/components/';

		$cssfile = $this->embed() . $this->stripfontsrc(File::get($src));

		if (!File::exists($dst)) {
        	File::makeDirectory($dst, 0755, true);
    	}

		File::put($dst.'fontello.scss',$cssfile);

		$this->info("saved: {$dst}.");

	}


	private function unzipper()
	{

		$zip = new \ZipArchive;
		$res = $zip->open( $this->download_path . $this->fontello );
		if ($res === true) 
		{
			$zip->extractTo( $this->download_path );
			$zip->close();

			if ( !File::exists($this->zip_path))
			{
				return false;
			}
			return true;
		}

		return false;
	}

	private function copy_zip()
	{

		$src= $this->zip_path . '.zip';
		$dst= config('build.tools.package_path').$this->fontname.'/src/resources/assets/fontello.zip';

		if (File::exists(config('build.tools.package_path').$this->fontname))
		{
			File::copy($src, $dst);
		}

		$this->info("copied: {$src} => {$dst}.");
	}


	private function stripfontsrc($str)
	{
		return substr($str, strpos($str, '[class'));
	}

	private function embed()
	{

		$fontfile= $this->zip_path . '/font/' . $this->fontname;

		$base64_woff = base64_encode(File::get( $fontfile.'.woff') );
		$base64_ttf  = base64_encode(File::get( $fontfile.'.ttf')  );

		$css = "@font-face {\n";
		$css.= "    font-family: '{$this->fontname}';\n";
		$css.= "    src: url(data:application/font-woff;charset=utf-8;base64,{$base64_woff}) format('woff');\n";
		// $css.= "    src: url(data:application/font-woff;charset=utf-8;base64,{$base64_woff}) format('woff'),\n";
		// $css.= "         url(data:application/font-ttf;charset=utf-8;base64,{$base64_ttf}) format('truetype');\n";
		$css.= "    font-weight: normal;\n";
		$css.= "    font-style: normal;\n";
		$css.= "}\n";

		return $css;
	}

	private function getlatest()
	{
		$list = $this->fontellozips();
		
		$otime = 0;
		$ffile = '';
		foreach ($list as $entry)
		{
			$ftime = filemtime( $this->download_path . $entry );
			if ( $ftime > $otime ){
				$otime= $ftime;
				$ffile= $entry;
			}
		}
		$this->fontello  = $ffile;
		$this->zip_path  = $this->download_path . str_replace(".zip", "", $ffile);
		$this->font_path = $this->zip_path . DIRECTORY_SEPARATOR . 'font';
		return $ffile; 
	}

	private function getfontname()
	{
		$ttf = array_filter(
			array_map('basename', File::files($this->font_path)),
			function($filename){
				return ( substr($filename, -4) === '.ttf');
			}
		);
		$this->fontname= str_replace(".ttf", "", end($ttf));

		return $this->fontname; 
	}

	private function fontellozips()
	{
	
		$zips = array_filter(
			array_map('basename', File::files($this->download_path)),
			function($filename){
				return (( substr($filename, 0, 8) === 'fontello') 
					&&  ( substr($filename, -4)   === '.zip'));
			}
		);

		return $zips; 
	}

}
