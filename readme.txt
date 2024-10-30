=== Course Session For Sensei ===
Contributors: opendsi
Tags: course, session, date, sensei, visibility
Requires at least: 4.9
Tested up to: 5.0.3
Stable tag: 1.2.6
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Manage sessions and group of sessions for your Sensei LMS courses.

== Description ==

Create sessions (specific date) and organize them in a group of sessions linked to your Sensei course. Sessions are displayed in between the course Modules and / or Lessons. The plugin also lets you restrict Module access before a certain date (for example, the previous session date).

Made for the [Sensei](https://woocommerce.com/products/sensei/) Learning Management System plugin for WordPress.

== Installation ==

Installing "Course Session For Sensei" can be done either by searching for "Course Session For Sensei" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

0. Check the 'Sensei' plugin is active through the 'Plugins' menu in WordPress or install it
1. Download the plugin via WordPress.org
2. Upload the ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
3. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. Add New Course Session screen.
2. Group of Sessions screen. Add and manage Group of Sessions.
3. Session Order screen. Drag and drop sessions and place it in between the course lessons and modules.
4. Session display in Course view, front side.
5. Restrict Module access. Here It will not be available to students before the 21st of June.

== Frequently Asked Questions ==

= Does this plugin depend on any others? =

Yes. It depends on the [Sensei](https://woocommerce.com/products/sensei/) plugin. It was tested with Sensei 1.9.16 & 1.9.19.


= Does this create new database tables? =

No. There are no new database tables with this plugin.


= Does this load additional JS or CSS files ? =

Yes. It loads the `admin.css`, `jquery-ui.css`, `admin.min.js`, and `admin-order.min.js` files on the admin side and the `frontend.css` file on the front side.


= Is the plugin compatible with WordPress Multisite (MU)? =

Yes. The Wizard plugin was successfully tested on WordPress Multisite. You will have to activate the plugin separately for each site.


= How can I add a Session to a Group of Sessions? =

First, add a Group of Sessions and associate it to an existing Sensei course. Then, add or edit a Session. On the right side of the screen, you will notice a box where you can select the Group of Sessions.


= How can I restrict module access so students cannot view its content before the opening date? =

You can restrict the module access so students cannot view its content before a specific date. Go to the 'Courses > Modules' menu and edit the module. On this screen, you can both set the opening date and whether the content access is restricted. Those options are available for every course associated to the module.


= Is the plugin translated? =

Yes. It is translated in French (fr_FR).
You will find the translation files in the `lang/` folder.
New translations are welcome at https://translate.wordpress.org/projects/wp-plugins/course-session-for-sensei


= Where can I get support? =

Buy support packs at https://www.open-dsi.fr


== Changelog ==

= 1.2.6 =
* 2018.10.29
* Fix Course Sessions template (order when before module)

= 1.2.5 =
* 2018.10.25
* Fix Sensei plugin detection (now detects sensei/woothemes-sensei.php)

= 1.2.4 =
* 2018.09.19
* Fix all Course Sessions displaying when none added yet

= 1.2.3 =
* 2018.09.18
* Fix Course Session display when no or empty module

= 1.2.2 =
* 2018.09.14
* Fix Unordered Course Session display

= 1.2.1 =
* 2018.09.13
* Fix Restricted module lesson access

= 1.2.0 =
* 2018.06.21
* Fix Group of Sessions template display when no Sessions
* Display Group of Sessions description
* Fix French translation

= 1.1.0 =
* 2018-06-12
* Initial release
