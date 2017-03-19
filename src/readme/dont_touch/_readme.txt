=== Waterproof Wrap Query ===
Tags: shortcode,wrapper,widget,get_posts,get_terms,lists,listing
Donate link: http://waterproof-webdesign.info/donate
Contributors: jhotadhari
Tested up to: 4.7.2
Requires at least: 4.7
Stable tag: trunk
License: GNU General Public License v2
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Wrap your posts or terms in something fancy!


== Description ==
You need any of the "Waterproof wrapper" Plugins to use this Plugin. 

= Plugin contains =
* [wrap_query] shortcode helps to parse arguments as a JSON formated array to the get_posts() or get_terms() function, wraps the results in something fancy and pastes the listing in the content.
Its basicly documented in the "Waterproof [wrap_query] shortcode docs" metabox on the post edit sceen.
* Settings Page: Options for this Plugin and for all active "Waterproof wrapper" Plugins.
* Functionality to disable the singular post view on frontend: Option disabled on default (Settings Page). Controlled in "Wpwq Options" Metabox on the post edit sceen.
* Extra fields for term/category edit screens: Option disabled on default (Settings Page).
* Widget to create menu depending on [wrap_query] shortcodes.

> Plugin on [GitHub](https://github.com/jhotadhari/waterproof-wrap-query)
		
= Compatible with =
* qTranslateX

= Requirements =
* php 5.6
* Any Waterproof wrapper

= Thanks for beautiful ressoucres =
* [CMB2](https://github.com/WebDevStudios/CMB2)
* [Integration CMB2-qTranslate](https://github.com/jmarceli/integration-cmb2-qtranslate)
* [CMB2 Taxonomy](https://github.com/jcchavezs/cmb2-taxonomy)
* This Plugin is based on the [generator-pluginboilerplate](https://github.com/jhotadhari/generator-pluginboilerplate)

== Installation ==
Requirements:
* php 5.6
* Any Waterproof wrapper

Upload and install this Plugin in the same way you'd install any other plugin.

== Screenshots ==
1. Options Page
2. Edit term/category screen with extra fields
3. Edit page screen: "Waterproof [wrap_query] shortcode docs" metabox and "Wpwq Options" metabox

== Changelog ==

= 0.0.3 =
bug fix: extract_shortcode_atts method, query_args property, exchange 'true'/'false' with boolen value
js: init the widget hover stuff on ajax complete
menu widget: added kind of support for terms


= 0.0.2 =
Edit readme

= 0.0.1 =
Hurray, first stable Version!

