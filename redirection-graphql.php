<?php

/**
 * The plugin bootstrap file.
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.urbaninsight.com
 * @since             0.9.0
 *
 * @wordpress-plugin
 * Plugin Name:       Redirection GraphQL Extension
 * Description:       Expose redirects set up in Redirection plugin to GraphQL API.
 * Version:           0.9
 * Author:            urbaninsight, bcupham
 * Author URI:        https://www.urbaninsight.com
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       redirection-graphql
 */

// Exit if accessed directly.
if (!defined('ABSPATH')) {
  exit;
}

function gqrd_init()
{

  if (true === gqrd_can_load_plugin()) {
    add_action('graphql_register_types', 'gqrd_register_graphql_fields');
  } else {
    /**
     * For users with lower capabilities, don't show the notice
     */
    if (!current_user_can('manage_options')) {
      return false;
    }
    add_action(
      'admin_notices',
      'gqrd_missing_plugins_notice'
    );
  }
}


/**
 * Check whether WP GraphQL and Redirection plugins are active
 *
 * @return bool
 * @since 0.3
 */
function gqrd_can_load_plugin()
{
  // Is WP GraphQL active?
  if (!class_exists('WPGraphQL')) {
    return 'WP GraphQL';
  }
  // Is Redirection active?
  if (!class_exists('Red_Item')) {
    return 'Redirection';
  }

  return true;
}


/**
 * Show admin notice to admins if this plugin is active but dependencies are missing
 * are not active
 *
 * @return bool
 */
function gqrd_missing_plugins_notice()
{
  $missing = gqrd_can_load_plugin();
  if (true === $missing) return;
?>
  <div class="error notice">
    <p><?php printf(esc_html('%1$s plugin must be active for the Redirection GraphQL Extension plugin to work.', 'redirection-graphql'), $missing); ?>
    </p>
  </div>
<?php

}

add_action(
  'plugins_loaded',
  function () {
    gqrd_init();
  }
);


/**
 * Create graphql "Redirects" field for Redirection plugin redirect data
 */

function gqrd_register_graphql_fields()
{

  register_graphql_object_type(
    'Redirect',
    [
      'description' => __('Redirection 301 redirects', 'redirection-graphql'),
      'fields'      => [
        'id' => [
          'type'        => 'Integer',
          'description' => __('Redirect ID', 'redirection-graphql')
        ],
        'url' => [
          'type'        => 'String',
          'description' => __('Source URL', 'redirection-graphql')
        ],
        'match_url'  => [
          'type'        => 'String',
          'description' => __('Match URL', 'redirection-graphql')
        ],
        // 'match_data'  => [
        //   'type'        => 'String',
        //   'description' => __('Match settings', 'redirection-graphql')
        // ],
        'flag_query' => [
          'type'        => 'String',
          'description' => __('Which query parameter matching to use. Allowed values: "ignore", "exact", "pass"', 'redirection-graphql')
        ],
        'flag_case' => [
          'type'        => 'Boolean',
          'description' => __('true for case insensitive matches, false otherwise', 'redirection-graphql')
        ],
        'flag_trailing' => [
          'type'        => 'Boolean',
          'description' => __('true to ignore trailing slashes, false otherwise', 'redirection-graphql')
        ],
        'flag_regex' => [
          'type'        => 'Boolean',
          'description' => __('true for regular expression in the source URL string, false otherwise. Same as the "regex" field AFAIK.', 'redirection-graphql')
        ],
        'action_code' => [
          'type'        => 'String',
          'description' => __('The HTTP code to return', 'redirection-graphql')
        ],
        'action_type'  => [
          'type'        => 'String',
          'description' => __('What to do when the URL is matched', 'redirection-graphql')
        ],
        'action_data'  => [
          'type'        => 'String',
          'description' => __('Any data associated with the action_type. For example, the target URL', 'redirection-graphql')
        ],
        'match_type'  => [
          'type'        => 'String',
          'description' => __('What URL matching to use', 'redirection-graphql')
        ],
        'title'       => [
          'type'        => 'String',
          'description' => __('Optional redirect title', 'redirection-graphql')
        ],
        'hits'       => [
          'type'        => 'Integer',
          'description' => __('Number of hits (irrelevant for headless WP)', 'redirection-graphql')
        ],
        'regex'       => [
          'type'        => 'Boolean',
          'description' => __('Whether the redirect use regex', 'redirection-graphql')
        ],
        'group_id'       => [
          'type'        => 'Integer',
          'description' => __('Redirects group', 'redirection-graphql')
        ],
        'position'       => [
          'type'        => 'Integer',
          'description' => __('Position in Redirection, for determining precedence', 'redirection-graphql')
        ],
        'last_access'       => [
          'type'        => 'String',
          'description' => __('Date last accessed (irrelevant for headless WP)', 'redirection-graphql')
        ],
        'enabled'       => [
          'type'        => 'Boolean',
          'description' => __('Whether redirect is enabled', 'redirection-graphql')
        ],
      ],
    ]
  );

  register_graphql_field(
    'RootQuery',
    'redirects',
    [
      'description' => __('Return list of redirects from the Redirection plugin', 'redirection-graphql'),
      'type'        => ['list_of' => 'redirect'],
      'args'        => [
        'id' => [
          'type' => 'String',
          'description' => __('The ID of the redirect', 'redirection-graphql')
        ],
      ],
      'resolve'     => function ($root, $args) {

        $redirect_data = [];
        $redirects = [];

        if (isset($args['id'])) {
          $redirect_data[] = Red_Item::get_by_id($args['id']);

          if (false === $redirect_data[0]) return [];
        } else {
          $redirect_data = Red_Item::get_all();
        }

        foreach ($redirect_data as $r) {


          $redirect_json = $r->to_json();
          $redirect_json['action_data'] = $r->get_action_data();

          $match_data = $r->get_match_data();
          $redirect_json['flag_regex'] = $match_data['source']['flag_regex'];
          $redirect_json['flag_query'] = $match_data['source']['flag_query'];
          $redirect_json['flag_case'] = $match_data['source']['flag_case'];
          $redirect_json['flag_trailing'] = $match_data['source']['flag_trailing'];

          // $redirect_json['match_data'] = $r->get_match_data();

          $redirects[] = $redirect_json;
        }

        return $redirects;
      }
    ]
  );
}
