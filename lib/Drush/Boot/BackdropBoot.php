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
    drupal_bootstrap(DRUPAL_BOOTSTRAP_DATABASE);
    parent::bootstrap_drupal_database();
  }

  function bootstrap_backdrop_configuration() {
    backdrop_bootstrap(BACKDROP_BOOTSTRAP_CONFIGURATION);

    // Unset backdrop error handler and restore drush's one.
    restore_error_handler();

    parent::bootstrap_backdrop_configuration();
  }

  function bootstrap_backdrop_full() {
    if (!drush_get_context('DRUSH_QUIET', FALSE)) {
      ob_start();
    }
    backdrop_bootstrap(BACKDROP_BOOTSTRAP_FULL);
    if (!drush_get_context('DRUSH_QUIET', FALSE)) {
      ob_end_clean();
    }

    parent::bootstrap_backdrop_full();
  }

  /**
   * @param $command
   *   drush cli parameter, e.g. cache-clear
   */
  function backdrop_drush_command_alter(&$command) {
    if ($command['command'] == 'drupal-major-version') {
      $command['command'] = 'backdrop-major-version';
      $command['command-hook'] = 'backdrop-major-version';
      $command['primary function'] = FALSE;
      $command['arguments'] = array();
      drush_set_command($command);
    }
  }
}
