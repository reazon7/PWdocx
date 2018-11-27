<?php

namespace REAZON\PWdocx;

use Illuminate\Support\ServiceProvider;

class PWdocxServiceProvider extends ServiceProvider
{
	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->publishes([
			__DIR__ . '/config/config.php' => config_path('pwdocx.php'),
		], 'config');
	}

	/**
	 * Register any application services.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->mergeConfigFrom(__DIR__ . '/config/config.php', 'pwdocx');
	}
}
