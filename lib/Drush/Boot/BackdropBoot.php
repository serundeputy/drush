<?php

namespace Drush\Boot;

class BackdropBoot extends DrupalBoot {

  function valid_root($path) {
    if (!empty($path) && is_dir($path) && file_exists($path . '/index.php')) {
      // Backdrop root.
      // We check for the presence of 'core/modules/field/field.module' to differentiate this from a D6 site
      $candidate = 'core/includes/common.inc';
      if (file_exists($path . '/' . $candidate) && file_exists($path . '/core/misc/backdrop.js') && file_exists($path . '/core/modules/field/field.module')) {
        return $candidate;
      }
    }
  }

  function get_profile() {
    return backdrop_get_profile();
  }

  function add_logger() {
    // If needed, prod module_implements() to recognize our system_watchdog() implementation.
    $dogs = drush_module_implements('watchdog');
    if (!in_array('system', $dogs)) {
      // Note that this resets module_implements cache.
      drush_module_implements('watchdog', FALSE, TRUE);
    }
  }

  function contrib_modules_paths() {
    return array(
      conf_path() . '/modules',
      '/modules',
    );
  }

  function contrib_themes_paths() {
    return array(
      conf_path() . '/themes',
      '/themes',
    );
  }

  function bootstrap_drupal_core($backdrop_root) {
    define('BACKDROP_ROOT', $backdrop_root);
    $core = BACKDROP_ROOT;

    return $core;
  }

  function bootstrap_drupal_database() {
    drupal_bootstrap(BACKDROP_BOOTSTRAP_DATABASE);
    parent::bootstrap_drupal_database();
  }

  function bootstrap_drupal_configuration() {
    /*$this->request = Request::createFromGlobals();
    $classloader = drush_drupal_load_autoloader(BACKDROP_ROOT);
    $this->kernel = DrupalKernel::createFromRequest($this->request, $classloader, 'prod');*/
    drupal_bootstrap(BACKDROP_BOOTSTRAP_CONFIGURATION);

    // Unset backdrop error handler and restore drush's one.
    restore_error_handler();

    parent::bootstrap_drupal_configuration();
  }

  function bootstrap_drupal_full() {
    if (!drush_get_context('DRUSH_QUIET', FALSE)) {
      ob_start();
    }
    drupal_bootstrap(BACKDROP_BOOTSTRAP_FULL);
    if (!drush_get_context('DRUSH_QUIET', FALSE)) {
      ob_end_clean();
    }

    parent::bootstrap_drupal_full();
  }

  /**
   * Override for Backdrop bootstrap phases.
   */
  function bootstrap_phases() {
    /*$phases = array(
      //DRUSH_BOOTSTRAP_DRUSH                  => 'bootstrap_drush',
      DRUSH_BACKDROP_BOOTSTRAP_CONFIGURATION,
      DRUSH_BACKDROP_BOOTSTRAP_PAGE_CACHE   ,
      DRUSH_BACKDROP_BOOTSTRAP_DATABASE     ,
      // This has an additional include in c,
      // so some additional work is expected,
      DRUSH_BACKDROP_BOOTSTRAP_LOCK       ,
      DRUSH_BACKDROP_BOOTSTRAP_VARIABLES    ,
      // This has an additional include in c,
      // so some additional work is expected,
      //DRUSH_BACKDROP_BOOTSTRAP_SESSION    ,
      DRUSH_BACKDROP_BOOTSTRAP_PAGE_HEADER  ,
      DRUSH_BACKDROP_BOOTSTRAP_LANGUAGE     ,
      DRUSH_BACKDROP_BOOTSTRAP_FULL         ,
    );*/
    $phases = array(
        //DRUSH_BOOTSTRAP_DRUSH                  => 'bootstrap_drush',
        DRUSH_BACKDROP_BOOTSTRAP_CONFIGURATION  => '_backdrop_bootstrap_configuration',
        DRUSH_BACKDROP_BOOTSTRAP_PAGE_CACHE     => '_backdrop_bootstrap_page_cache',
        DRUSH_BACKDROP_BOOTSTRAP_DATABASE       => '_backdrop_bootstrap_database',
        // This has an additional include in core/includes/bootstrap.inc
        // so some additional work is expected here.
        //DRUSH_BACKDROP_BOOTSTRAP_LOCK           => 'lock_initialize',
        DRUSH_BACKDROP_BOOTSTRAP_VARIABLES      => '_backdrop_bootstrap_variables',
        // This has an additional include in core/includes/bootstrap.inc
        // so some additional work is expected here.
        //DRUSH_BACKDROP_BOOTSTRAP_SESSION        => 'backdrop_session_initialize',
        DRUSH_BACKDROP_BOOTSTRAP_PAGE_HEADER    => '_backdrop_bootstrap_page_header',
        DRUSH_BACKDROP_BOOTSTRAP_LANGUAGE       => 'backdrop_language_initialize',
        DRUSH_BACKDROP_BOOTSTRAP_FULL           => '_backdrop_bootstrap_full',
    );
    return $phases;
  }

  /**
   * @param $command
   *   drush cli parameter, e.g. cache-clear
   */
  /*function backdrop_drush_command_alter(&$command) {
    if ($command['command'] == 'drupal-major-version') {
      $command['command'] = 'backdrop-major-version';
      $command['command-hook'] = 'backdrop-major-version';
      $command['primary function'] = FALSE;
      $command['arguments'] = array();
      drush_set_command($command);
    }
  }*/
}
