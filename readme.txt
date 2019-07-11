=== Exhibitors Code System ===
Tags: exhibitors, codes, dynamic and manual generating codes
Requires at least: 4.7
Tested up to: 5.3
Stable tag: 1.5
Requires PHP: 5.2.4

Another Wordpress plugin... which allows you to generate automatically or manual codes for exhibitors and more.

== Description ==

Another Wordpress plugin... which allows you to generate automatically or manual codes for exhibitors and check this codes.

== Installation ==

First of all You need to make form in Gravity Forms plugin. 

#### Creating a form for check entered code before access to form. 

Create new form in Gravity Forms and add that one field to form's fields:

```
type = Single Line Text
```

And add class:

```
invitation_code
```

* Add this field in confirmations for Form admin.

Thats all in Gravity Forms, now create a new Wordpress Page and set 'Page Template' to 'Exhibitors Code Checker'. Then insert in Your page content form You created in Gravity Forms.

Enjoy!

#### Creating a form for automaticaly generate invitation code for exhibitors. 

Create new form in Gravity Forms and add that one field to form's fields:

```
type = Single Line Text
```

And add class:

```
code
```

* Add this field in confirmations for exhibitor.

Go to plugin settings and set prefix for automatically generating exhibitors invitation code. For example:

```
XYZ2102
```

* Individual Code for Exhibitor is generating this way: 'prefix_you_set'+'amount of exhibitors in current form base'. For example:
```
XYZ21021
```

Set Your Grafity Form ID that You want to generate codes.

* Optional: Set Your own list of codes comma separated - **no comma at the end**

Thats all in Gravity Forms, now create a new Wordpress Page and set 'Page Template' to 'Exhibitors Code Maker'. Then insert in Your page content form You created in Gravity Forms.

Enjoy!

* Don't forget about link to Your Page Checker in notification for exhibitor.


== FAQ ==

= Can I set only one type of page or should I create both =

Yes, You can create one of two available pages (Code Maker or Code Checker).

= Can I set only dynamic generating or manually generated codes list?  =

Yes, You can use only one type of Codes List - manually entered or make them dynamic while user is registrating. But both of this options will work great as well. 


== Changelog ==

= 2.1 =
1 link 2 forms

= 2.0 =
Added VIP Codes functionality

= 1.9 =
Options Save problem solved

= 1.8 =
Added Exhibitors Code Checker

= 1.7 =
Code Optimize

= 1.6 =
Added CSV Import

= 1.5 =
Version with clean code and after optimizations.

= 1.4 =
Version with readme.md

= 1.3 =
Version with readme.txt

= 1.2 =
Version with AutoUpdate.

= 1.1 =
First version with dynamic inluding page template.

= 1.0 =
First stable version.