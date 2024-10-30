=== MJLK Link Tracker ===
Contributors: Matthew B. Jordan
Tags: link tracking,tracking,link
Requires at least: 3.0
Tested up to: 3.3
Stable tag: 1.2

Track inbound and outbound links

== Description ==

Ever wonder how many clicks a link on your website gets? Sometimes its good to know. MJLK Link Tracking is simple to use and easy to install.

See for your self: [mjlk href="http://mysite.com/my-link" notes="My Notes about this link"] My Link Text [/mjlk]

You can track the use of any link on (or off) your website by simply implementing the code above.

MJLK works by adding an "onmousedown" event to the `A` tag. When the link is clicked, MJLK switches the `HREF` value from the actual link to the tracking link..

MJLK Collects the following information from each click:

	Link To - The location the link is going to.	
	Link Referrer - The location the link is coming from.	
	IP Address - The Remote IP Address of the user.	
	Date and time of click
	Notes - An optional value to annotate between duplicate links in the same web page. This is a string value with a maximum of 200 chars.

== Installation ==

Installing MJLK.

	1. Unzip and upload the `mjlk-link-tracking` folder to the `/wp-content/plugins/` directory
	2. Activate the plugin through the 'Plugins' menu in WordPress
	3. If link switch is not working, edit the `mjlk/mjlk.js` file to properly reflect your wp-plugin directory
	4. Place the following code into any post or page: [mjlk href="LINK" notes="NOTE"]DISPLAY TEXT[/mjlk]

Attributes:
LINK (required):
	The full URL to the page you wish to link to.

NOTE (optional):
	Use this Attribute to differentiate between links, i.e. "top of myPost" and "bottom of myPost"

DISPLAY TEXT (required):
	The text that will be displayed in the users browser, i.e. "click Here"
		

== Changelog ==

1.2 (a) Added preventative measures for XSS and MySQL Injection to the MJLK core file. (b) Changed DB table collation to utf_8 on new installs.

1.1 Fixed a small problem with the JavaScript file that prevented MJLK from working out-of-the-box on standard WP instals.

1.0 First Release

0.5 Developed and tested.


== Upgrade Notice ==

= 1.1 =
Upgrade to MJLK version 1.2 ASAP to prevent XSS attacks.

= 1.0 =
Upgrade to MKLK version 1.2 ASAP to prevent XSS attacks.

= 0.5 =
Development Version - Upgrade ASAP
