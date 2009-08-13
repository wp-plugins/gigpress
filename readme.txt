=== GigPress ===
Contributors: mrherbivore
Donate link: http://gigpress.com/donate
Tags: concerts, bands, tours
Requires at least: 2.3.3
Tested up to: 2.8.2

GigPress provides an easy way for bands to list and manage their concerts and tours on their WordPress-powered website.

== Description ==

**GigPress is a WordPress plugin built specifically with musicians and performers in mind.**  Manage all of your upcoming and past performances or events right from within WordPress, and display them using simple shortcodes or template tags on your WordPress-powered website. You can optionally link each show or performance to a post, or automatically create a new post each time you add a new show.  Other features include:

* Ability to list both upcoming shows and past shows, chronologically rolled-over as tour dates pass
* Grouping of shows into tours
* Multiple-day shows
* Mark shows as Cancelled or Sold Out
* Auto-linking to Google Maps for venues with a street address
* Fields for show times, venue and ticket-buy URLs and phone numbers, age restrictions, ticket price, and notes
* An upcoming shows RSS feed
* Widget support
* Speedy data entry with menus that default to most recent-used positions, and the ability to duplicate shows
* Full Undo support for accidental deletions
* Export your data in CSV format at any time
* User-level access restrictions for use
* hCalendar markup for each show
* Outputs XHTML-compliant, semantic markup
* Fully internationalized - comes with Brazilian Portuguese, Bulgarian, Danish, Dutch, French, German, Italian, "Book" Norwegian, "New" Norwegian, Russian, Slovak, Spanish, Swedish and Ukranian language files

== Changelog ==

= 1.4.9 =

* Added Belarusian localization (thanks to M.Comfi)
* Shows on the same day are now further ordered by show time
* Fixed venue information toggle under IE 7

= 1.4.8 =

* Added German (thanks to David Scott), Slovak (thanks to Igor Rjabinin) and Ukranian (thanks to Vladimir Agafonkin) localization
* Fixed a bug where cancelled shows were not appearing in the admin when not associated with a tour

= 1.4.7 =

* Fixed a missing closing tag on the related show list.
* Improved compatibility with child themes when loading a custom gigpress.css file.
* Expanded year list to 1900 through 2050.
* Updated country list to comply with the ISO 3166-1 list of countries, dependent territories, and special areas of geographical interest.
* Added Russian translation (thanks to Ravi).

= 1.4.6 =

* Fixed a bug which caused the "stickiness" of the fields on the entry screen to be delayed by one refresh.  Each new show entry now immediately loads the previous show's sticky fields (date, country, tour).
* Fixed RSS feed validation errors when using GigPress in languages other than English.
* Added the classes "upcoming" and "archive" to the respective shows tables.

= 1.4.5 =

* Fixed a bug which was preventing shows from displaying under WordPress 2.3.x.
* Fixed a bug where the SOLD OUT label was not displaying in the past shows listings.
* Added Dutch translation (thanks to Martin Teley)

= 1.4.4 =

* Fixed a typo in the database upgrade check that was leading to about 30 extra queries being performed on every page load throughout WordPress.  Oops?
* Added the missing "notes" field to the show listings on Related Post entries and in the RSS feed
* Added an "Add a show" link to the WordPress 2.7 favourites menu
* Added a new shortcode parameter "limit" that will display only a chosen number of shows (only works when *not* segmenting by tour, or when used in conjunction with displaying a specific tour using the "tour" shortcode parameter)
* Added Bulgarian (thanks to Ivo Minchev) and Danish (thanks to Michael Tysk-Andersen) translations

= 1.4.2 =

* Fixed a couple of bugs when using the gigpress_upcoming() and gigpress_archive() template tags - these functions now need to be echoed, e.g. <?php echo gigpress_upcoming(); ?>
* Removed vestigial hard-coded "Tour" label on Related Post entries
* Fixed minor character entity issue when loading/updating settings

= 1.4.1 =

* Fixed a bug where shortcodes were outputting before any other post content, regardless of where they appeared in the post

= 1.4 =

* Complete show info can now be displayed within a show's related post entry (before or after post content)
* Option to automatically create a new related post when entering a new show
* Date and City can now be optionally linked to related shows
* Show can now be marked as CANCELLED or SOLD OUT
* Added fields for venue phone and box office
* Added ability to export all of your shows to a tab-separated CSV file
* You can now show a single tour using its ID in the gigpress_upcoming shortcode (see docs for more info)
* More language is now customizable through the Settings page
* Optional 24-hour clock display for show time on the entry screen
* Ability to enter archival shows back to 1960
* Added alternating class to sidebar listing
* Compatibility and styling updates for WordPress 2.7
* Dropped support for WordPress 2.2.3
* Probably some other stuff

= 1.3.4 =

* Fixed a bug that prevented language files from being loaded under WordPress 2.6
* Fixed an <abbr title="eXtensible HyperText Markup Language"><span class="abbr" title="eXtensible HyperText Markup Language">XHTML validation error in the upcoming/past shows table output

= 1.3.3 =

* Fixed a bug where past shows wouldn't appear in the admin in certain cases
* Revised and optimized the code that fetches recent posts for the "related post" drop-down when adding a show, as posts were being retrieved in the wrong order in some circumstances
* Added compatibility with WordPress 2.6's ability to relocate the wp-content directory

= 1.3.2 =

* Lowered the number of posts retrieved in the drop-down on the "Add a show" page to 100, as the previous 1000-post limit could cause PHP memory errors in some cases
* Fixed a bug where deleted shows were still appearing in the sidebar and RSS feed
* Added Basque, Hungarian and Norwegian translations

= 1.3.1 =

* Fixed a bug where the phrase "opens in a new window" that appears in the title attribute of certain links was not getting translated.
* Added German, Polish and Swedish translations

= 1.3 =

* New feature: associate each show with a post in WordPress
* New feature: copy any existing show to the "Add a show" screen for faster data entry
* Tours can now be reordered
* Added option to display tours before *or* after non-tour shows
* Added the option to *not* segment the tour listing into tours and individuals shows
* Added option to open Google maps, venue, and ticket-buy links in a new window
* The date, time, country and tour fields are now all "sticky," so their last-used values will be loaded into the "Add a show" form each time
* Added "undo" option immediately after deleting shows and tours
* Add visual cues for required fields on the "Add a show" screen
* GigPress will now look for a style sheet called gigpress.css in your current theme folder in order to load custom styles
* More styling fixes for visual compatibility with WordPress 2.5
* Dropped official support for Wordpress 2.1.3

= 1.2.7 =

* Added Spanish and French translation files
* Fixed a few text strings that weren't getting translated
* Add gettext() wrappers to month names, which will allow them to be translated by the core WordPress language file
* Removed some stray quotation marks in the welcome message
* Fixed a bug where under certain conditions the sidebar widget would not show the "no upcoming shows" message
* Added an extra span and class to the fields displayed in the "gigpress-info" cell of the shows table to allow for further styling flexibility
* The javascript used by GigPress in the WordPress admin will now *only* load on the GigPress "Add a show"  page to prevent potential conflict with other plugins' scripts
* Modified some of the markup and CSS in the admin area to better suit the forthcoming admin design in WordPress 2.5

= 1.2.6 =

* GigPress is now fully internationalized - language files for Italian, Hungarian, and Dutch included
* The 'show time' field can now be set to 'N/A' - if so it will not display
* Added option to choose your default country when adding new shows
* Fixed a bug where under certain configurations shows would move to the archive on the day of the show
* Changed default encoding of the database tables to UTF-8

= 1.2.2 =

* Fixed a bug where past shows would not display if there were no tours in the database
* Increased compatibility with certain configurations of MySQL 5

= 1.2.1 =

* The jQuery library used by GigPress was disabling the drag and drop on the WordPress widgets page.  GigPress now uses the jQuery version bundled with WordPress instead (but will load its own in WordPress versions prior to 2.2)

= 1.2 =

* Added a "time" field
* Added the ability to make shows span multiple days
* Added option to select a user-level required to use GigPress
* Add option to display a link to your upcoming shows page beneath the sidebar listing
* Fixed a bug where the sidebar listing wouldn't display any shows if the tour segmenting option was on, but there were no tours in the database
* The "Admittance" field will now not display if it's set to "Not sure"
* Fixed various issues in the countries list
* Display of the Country column can now be disabled
* Added element IDs to the header row of each tour in shows table (eg. #tour-2)
* Updated Options page to refelect new features
* Added a <link> element to each item in the RSS feed, linked to the page set on the options page

= 1.1.1 =

* Fixed a stray tag in the code that mangled the sidebar output when using it via the template tag with tour segmentation active

= 1.1 =

* Added RSS feed for upcoming shows
* Add option to split gigpress_sidebar output into tours
* Added filter on all output in the admin and in the template functions to strip slashes and encode HTML entities (oops!)

= 1.0 = 

* Initial release

== Installation ==

1. Upload the `gigpress` folder to the `/wp-content/plugins/` directory on your web server
2. Activate the plugin through the 'Plugins' admin menu in WordPress
3. To list upcoming shows, simply create a new page and put `[gigpress_upcoming]` in the page content.
4. To list past shows, create a new page and put `[gigpress_archive]` in the page content.
5. To use the GigPress sidebar widget, simply activate it from your Widget configuration screen, set your options, and save.
6. For detailed instructions, including using template tags in your theme templates, see the documentation: <http://gigpress.com/docs>

== UPGRADING ==

1. Deactivate GigPress on the Plugins page of your Wordpress administration panel.

2. Delete the `gigpress` folder from your `/wp-content/plugins/` directory.

3. Upload the new `gigpress` folder to your`/wp-content/plugins` directory.

4. Activate GigPress on the Plugins page of your Wordpress administration panel.

== FOR MORE INFO ==

Visit the plugin website at <http://gigpress.com> for the latest updates, or to report bugs, suggest features, etc.

== Screenshots ==

1. GigPress admin: "Add a show" page (WP 2.7)
2. GigPress admin: "Add a show" page (WP 2.6)
3. GigPress admin: "Upcoming shows" page (WP 2.7)
4. GigPress admin: "Upcoming shows" page (WP 2.6)
5. GigPress admin: "Manage tours" page (WP 2.7)