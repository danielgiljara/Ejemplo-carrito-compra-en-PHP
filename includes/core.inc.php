<?php

/**
 * @author Daniel Gil Jara (danielgiljara@gmail.com)
 */

/**
 * Database settings.
 */
define('DB_HOST', 'localhost');
define('DB_USER', '');
define('DB_PASSWORD', '');
define('DB_NAME', '');

/**
 * Start session variables management.
 */
session_start();

/**
 * Start system messages variable.
 */
$_SESSION['messages'] = isset_or($_SESSION['messages'], array('error' => array(), 'status' => array()));

/**
 * Connect to database.
 *
 * @todo Remove devel settings.
 */
mysql_connect(DB_HOST, DB_USER, DB_PASSWORD);
mysql_select_db('tienda1');

/**
 * Set character encoding and locale database settings.
 */
mysql_query("SET NAMES utf8");
mysql_query("SET lc_time_names = 'es_ES'");		

/**
 * Executes a SQL sentence and fetch an array with results.
 *
 * @param string $sql
 *   SQL sentence to execute.
 *
 * @return
 *   An array with results.
 */
function db_exec($sql) {
  $query = mysql_query($sql);

  $rows = array();
  while ($row = mysql_fetch_array($query)) {
    $rows[] = $row;
  }
  return $rows;
}

/**
 * Returns a variable if is set or default value
 * instead.
 */
function isset_or(&$variable, $default = NULL) {
  return isset($variable) ? $variable : $default;
}

/**
 * Load an PHP/HTML template passing variables to its context.
 *
 * @param $template
 *   String with template name. Templates must be saved on
 *   'templates' with a name like template_name.tpl.php.
 * @param $variables
 *   An associative array with template needed variables.
 *
 * @return
 *   HTML content with themed data.
 */
function template($template, $variables) {
  extract($variables, EXTR_SKIP); // Extract the variables to a local namespace
  ob_start(); // Start output buffering
  include "templates/$template.tpl.php"; // Include the file
  $contents = ob_get_contents(); // Get the contents of the buffer
  ob_end_clean(); // End buffering and discard
  return $contents; // Return the contents
}

/**
 * Verify the syntax of the given e-mail address.
 *
 * Empty e-mail addresses are allowed. See RFC 2822 for details.
 *
 * This function is borrowed from Drupal.
 *
 * @param $mail
 *   A string containing an e-mail address.
 *
 * @return
 *   TRUE if the address is in a valid format.
 */
function valid_email_address($mail) {
  // Backward compatibility for PHP in this example.
  //
  // TODO: Accurate this version number. I remember to be forced to develop
  // and "old version" when I push the code into the final server.
  // It's a shame not to use a php genuine validation function when available,
  // I'm sure than must be most faster because is not a data checking function
  // with interpreted code such in the old version of PHP.
  static $php520;
  if (!isset($php520)) {
    $php520 = version_compare(PHP_VERSION, '5.2.0', '>=');
  }
  if ($php520) {
    $user = '[a-zA-Z0-9_\-\.\+\^!#\$%&*+\/\=\?\`\|\{\}~\']+';
    $domain = '(?:(?:[a-zA-Z0-9]|[a-zA-Z0-9][a-zA-Z0-9\-]*[a-zA-Z0-9])\.?)+';
    $ipv4 = '[0-9]{1,3}(\.[0-9]{1,3}){3}';
    $ipv6 = '[0-9a-fA-F]{1,4}(\:[0-9a-fA-F]{1,4}){7}';

    return preg_match("/^$user@($domain|(\[($ipv4|$ipv6)\]))$/", $mail);
  }

  return (bool) filter_var($mail, FILTER_VALIDATE_EMAIL);
}
