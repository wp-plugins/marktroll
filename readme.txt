=== MarkTroll ===
Contributors: h16
Donate link: http://h16free.com/me-contacter
Tags: comments, spam, hellban, shadowban, trolls, troll, trolling, banning, ban
Requires at least: 4.0.1
Tested up to: 4.1.1
Stable tag: trunk
License: GNU
License URI: http://www.gnu.org/licenses/gpl.html

MarkTroll is a WordPress plugin that will let you shadowban (hellban) any unlogged (regular) commenter on your blog.

== Description ==

MarkTroll is a simple WordPress plugin that implements user [shadowbanning](http://en.wikipedia.org/wiki/Hellbanning) (or hellbanning) for comments.

Comments by shadowbanned users will be invisible to all other users. However, the shadowbanned users will continue to see their own comments, hopefully oblivious to the fact that they've been shadowbanned. This is non-destructive in that changes are not made to the comments themselves. Shadowbanned comments are still saved to the database and visible to admins in the dashboard comments listing. Note that contrary to other plugins, Marktroll *does not* expect the commenting user to be logged to be shadowbanned. 

If you disable this plugin, showbanned comments will become visible to all users.

**Licensing**	

All code is released under The GNU License. Please see LICENSE.txt.

== Installation ==

**Installation**

1. Upload the MarkTroll folder to your '/wp-content/plugins/' directory
2. Activate the plugin through the 'Plugins' menu in WordPress

**To shadowban a user:**

1. In the comments section of the admin panel, you can "Mark as troll" (this option is added with the regular options to Unapprove, Edit, Quick Edit, Reply, Spam, Trash and so on)
2. The user, IP and email is then added to the Troll list, available in Settings/MarkTroll

To remove a user from shadowban, remove it from the Troll list (pseudo, IP and email).

== Frequently Asked Questions ==

None so far

== Changelog ==

= 0.1 =
* Shadowban list (troll list) in control panel
* Shadowbanned user's comment is invisible to all but the user

== Upgrade Notice ==

= 0.1 =
First version. No upgrade notices.