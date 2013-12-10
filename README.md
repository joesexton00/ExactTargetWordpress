Exact-Target-Wordpress
======================

A plugin for Wordpress that will push user data to Exact Target.  When a user accounts are created and updated on your website, the user data can be sent to Exact Target.  If a subscriber already exists in Exact Target then any subscriber attributes specified in the plugin settings will be updated using Wordpress user data, otherwise a new subscriber is created.  Subscribers can be added to one or more mailing lists automatically as well.

[I develop web applications in Minneapolis, MN](http://www.josephmsexton.com)

Requirements
------------

A version of php at or above 5.2.0.

Installation
------------

Copy the exact-target directory into your Wordpress installations wp-content/plugins directory.  Navigate to your installations wp-admin -> Plugins page and activate the Exact Target User Integration plugin.  After activated a Settings -> Exact Target menu is available where you need to enter your Exact Target credentials, mailing list ids, and the user attributes to push to Exact Target.