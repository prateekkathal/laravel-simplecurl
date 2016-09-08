<?php

namespace PrateekKathal\SimpleCurl;

use Illuminate\Support\ServiceProvider;

class SimpleCurlServiceProvider extends ServiceProvider {

  /**
   * Bootstrap the application services.
   *
   * @return void
   */
  public function boot() {
    //
  }

  /**
   * Register the application services.
   *
   * @return void
   */
  public function register() {
    $this->registerSimpleCurl();
  }

  /**
   * Registers Facade
   *
   * @return SimpleCurl
   */
  private function registerSimpleCurl() {
    $this->app->bind('simplecurl', function ($app) {
      return new SimpleCurl($app);
    });
  }

}
