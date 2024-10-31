=== Plugin Name ===
Contributors: Lars.D.Rasmussen
Donate link: http://www.rexcrawler.com/go/donate/
Tags: rexCrawler, spider, crawler, regex, regular expression
Requires at least: 3.0.1
Tested up to: 3.0.1
Stable tag: 1.0.15

rexCrawler is a regular expression web-crawler plugin. Able to parse websites, save data and show them on pages.

== Description ==

rexCrawler uses regular expressions to crawl specified websites.
Websites are saved in user-defined groups, which can then be showed on wordpress pages/posts using shortcodes.
The data crawled by rexCrawler is saved in the WP database in custom tables. Once data has been crawled, it can be inserted
into a page using the shortcode [rexCrawler]. Refer to the documentation on [http://www.rexcrawler.com](http://www.rexcrawler.com) for more information on how to use rexCrawler.

== Installation ==

1. Upload `rexCrawler`-folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Crawl data from the administration menu
4. Place `[rexCrawler]` in your pages/posts
- Refer to the [Documentation](http://www.rexcrawler.com/go/documentation/) for more information and examples of use.

== Frequently Asked Questions ==

None yet - do you have any?!

== Screenshots ==


== Changelog ==
= 1.0.15 =
* rexCrawler can now be used in text-widgets aswell

= 1.0.14 =
* Bug that caused the output filtering to fail when not using all filter-methods fixed
* Cleaning up the administration javascript code
* Updated danish localization

= 1.0.13 =
* Fixed an error when selecting a stylesheet for editing
* Cleaned up the output-script a bit

= 1.0.12 =
* Added stylesheet name generator for use in classes
* Fixed a bug which caused the admin stylesheet not to be included in some cases

= 1.0.11 =
* Bit of cleaning up the code
* AJAX added to the output tables when using forms

= 1.0.10 =
* Added a "Database check" on the main options page
* Added database installation if tables are missing

= 1.0.9 =
* Changed output-stylesheet again

= 1.0.8 =
* Redid the whole output-stylesheet thing
* Fixed a few typo's in danish localization

= 1.0.7 =
* Minor fixes

= 1.0.6 =
* Fixed bug where the database-tables wasn't created at activation

= 1.0.5 =
* Fixed bug where the selected default stylesheet wasnt shown
* Added table-layout parameter (Called with the parameter name 'table' - normal/list layout = 1)
* Added another table layout (ID 2)
* Possible to choose default table layour

= 1.0.4 =
* Fixed stylesheet inclusion error

= 1.0.3 =
* Trying again. Danish translation!

= 1.0.2 =
* Danish translation

= 1.0.1 =
* Readme updated

= 1.0 =
* First upload!

== Upgrade Notice ==
= 1.0.15 =
* Text-widget support

= 1.0.14 =
* If you want to be able to filter output data without having all forms at the page at the same time, you'll need this update.

= 1.0.13 =
* 1.0.12 introduced a bug, which made it impossible to edit stylesheets - this has been fixed with this release.

= 1.0.12 =
* Administration panel simplified due to proper CSS now
* Name-sanitizer when editing stylesheets, for easier class-generating.

= 1.0.11 =
* Added AJAX to the output tables, which helps on user-friendlyness.

= 1.0.10 =
* If you have trouble using rexCrawler, the new database check will make sure your database is up to date.

= 1.0.9 =
* If you're not experiencing trouble with your stylesheets, there's no need to update yet.

= 1.0.8 =
* To use output styles you might need to update to this version of rexCrawler.

= 1.0.6 =
* If you're experiencing trouble using rexCrawler, try deactivating it, upgrading and re-activating it. This should fix the problem if the database tables doesn't exist.

= 1.0.5 =
* New features! Why not?
* Better table-designing possibilities.

= 1.0.4 =
* Upgrade needed if you're going to use stylesheets.

= 1.0 =
:)