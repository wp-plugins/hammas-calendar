=== Plugin Name ===
Contributors:innovaatik 
Donate link: 
Tags: hammas, innovaatik, dental software, dentist, online, scheduling
Requires at least: 3.6.1
Tested up to: 4.1
Stable tag: 1.3.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

This plugin allows users of dental software Hammas to add online scheduling widget/add-on for their Wordpress based webpage. 

== Description ==

Plugin allows web users to interact with dental software Hammas to add and delete appointments. Users are identified by 
using Estonian ID card or Estonian mobile-ID service. For more information about how to get start can be found from [the official website](http://www.innomed.ee/)

== Installation ==

1. Uncompress the download package
1. Upload folder including all files and sub directories to the `/wp-content/plugins/` directory.
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Open the plugin settings and fill out the empty fields. If there is any fields you don't have correct value for please contact with Hammas support.  
1. Place tags into page content to activate the plugin. `(e.g [hp-calendar])`
1. FYI: There is additional information on the plugins configuration page. 

== Frequently Asked Questions ==
= What is Hammas and how it works? =
Please visit [the official website](http://www.innomed.ee/)

== Screenshots ==

1. Main interface of online scheduling that will be displayed on the webpage. 
2. Verification of appointment and identification page.
3. Your appointment management page that allows to remove existing appointments. 

== Changelog ==
= 1.3.0 =
* New feature: Ability to add attribute default_service to Wordpress shortcode.
When the default service is defined widget will auto-selects service and query-s
 open slots from current month. Also multiple service codes can be defined and the 
first available code found from dropdown will be selected. If non of the codes are found 
the logic will select the top service of dropdown. I.e [hp-calendar default_service="3,123"].
Service codes can be found by inspecting the dropdown menu <option> tag values. 
* Also at the this plug-in is released 2 new behavioral changes will be made
1. First free slot in a month will be auto selected.
2. If there is no open slots in the month then informative message will be shown. 

= 1.2.9 = 
* Localization update and easier instructions is configuration. 

= 1.2.8 = 
* Wordpress update compatibility update.

= 1.2.7 = 
* Optimization: Plugin is now using minified versions of js libraries and css styles

= 1.2.6 = 
* Optimization: Scripts and styles are now loaded only when Calendar is displayed. 

= 1.2.5 = 
* Minor backend api communication change.

= 1.2.4 =
* Compatibility update for wordpress 4.0

= 1.2.3 =
* Minor internal code enhancement.

= 1.2.2 = 
* Better handling in case there is no logo url. 

= 1.2.1 = 
* Compatibility update with new Wordpress 3.9

= 1.2 =
* First version that's published in Wordpress repository.
* Added option to force interface language if wordpress localization isn't used properly.

== Upgrade Notice ==

* No special steps to add here at the moment. 
