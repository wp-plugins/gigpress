=== GigPress ===
Contributors: mrherbivore
Donate link: http://gigpress.com/donate
Tags: concerts, bands, tours
Requires at least: 2.1.3
Tested up to: 2.3.3

GigPress provides an easy way for bands to list and manage their concerts and tours on their WordPress-powered website.

== Description ==

**GigPress is a WordPress plugin built specifically for touring bands.** As a website developer who was often building websites for independent bands -- and always using WordPress as CMS of choice -- I was constantly frustrated by the lack of a simple, viable method of incorporating live shows and tours into these websites. So I decided to brush up on my PHP and build my own.

###Features###

GigPress incorporates all the common requests you might have for a plugin of its ilk, including:

* [NEW] Fully internationalized
* Ability to list both upcoming shows and past shows, chronologically rolled-over as tour dates pass
* Grouping of shows into nameable tours
* Multiple-day shows
* An upcoming shows RSS feed
* hCalendar-rigged
* Auto-linking to Google Maps for venues with a street address
* Fields for show times, venue and ticket-buy URLs, age restrictions, ticket price, and notes
* Built-in functions to display upcoming shows, past shows, and a sidebar listing
* User-level access restrictions for use
* Widget support
* Outputs XHTML-compliant, semantic markup

== Installation ==

1. Upload the `gigpress` folder to the `/wp-content/plugins/` directory on your web server
2. Activate the plugin through the 'Plugins' admin menu in WordPress
3. To list upcoming shows, simply create a new page and put `[gigpress_upcoming]` in the page content.
4. To list past shows, create a new page and put `[gigpress_archive]` in the page content.
5. To use the GigPress sidebar widget, simply drag it into the widget area while on your Widget configuration screen. Options for the GigPress widget are set on the main GigPress Options page.
6. For detailed instructions, including using template tags in your theme templates, see the documentation: <http://gigpress.com/docs>

== UPGRADING ==

1. Deactivate GigPress on the Plugins page of your Wordpress administration panel.

2. Delete the `gigpress` folder from your `/wp-content/plugins/` directory.

3. Upload the new `gigpress` folder to your`/wp-content/plugis` directory.

4. Activate GigPress on the Plugins page of your Wordpress administration panel.

== FOR MORE INFO ==

Visit the plugin website at <http://gigpress.com> for the latest updates, or to report bugs, suggest features, etc.

== Screenshots ==

1. GigPress admin: "Add a show" page
2. GigPress admin: "Upcoming shows" page
3. GigPress admin: "Manage tours" page
4. GigPress admin: "Past shows page
5. GigPress admin: Options page