=== Redirection GraphQL Extension ===
Contributors: urbaninsight, bcupham
Tags: redirection, graphql, gatsby, redirects
Requires at least: 5.1.0
Tested up to: 6.0
Requires PHP: 7.0
Stable tag: 0.9
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Expose redirects set up in Redirection plugin to GraphQL API. 

== Description ==

This plugin creates a graphQL object and related fields for redirects created with the Redirection plugin for WordPress. It was developed for use with GatsbyJS, but should be compatible with other graphQL applications. 

Please note that additional code in the consuming application is needed to process these fields and create redirects. For Gatsby projects, check out our related Gatsby plugin in the Gatsby plugin library. 

Please also note that many redirect rules provided by Redirection may not be applicable for a headless app. For example, redirect rules based on whether a user is logged in. This plugin provides all of those rules by default, it is up to your consuming application to handle the different types. 

This is not an official extension of Redirection. Redirection redirects are also available via the Redirection REST API. 

This plugin requires the Redirection plugin and the WPGraphQL plugin. 

== Installation ==

1. Install this plugin from the directory or by downloading it directly from here and uploading it.
2. Activate the plugin. 
3. Your redirects should be available at the rootquery as "redirects". 

== Changelog == 

= 0.9 =
* Beta launch. 