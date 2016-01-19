FAQ
====

Why the installation requires so many DI extensions?
----

First not all of them are exactly required. The installation shows the recommended configuration. You can actually ommit PropertyAccess, CSRF protection and even Validator. But do you really want to? Second while I could have put the rest into one giant extension most of the stuff is general and not directly related to forms so I decided to split it. Besides most of the extensions are actually very very simple.

Why Twig instead of Latte?
----

Actually I've tried to use Latte to render the forms. Unfortunately Latte has proven to be extremely inadequate for this. For one latte lacks an API to load a whole template and then render just one of its blocks which is essential for rendering of symfony forms. This means you would need a separate template for each block which would leave us with about a hundred small latte files. Also the default themes contain many cases of modifying some variables before actually using them (such as adding a class to a variable that may not yet exist at the time). This would make the latte templates very complicated because there is no good way to simplify this in latte.

If you try to implement it yourself I'll be happy to give you some advice about where to begin and later have a look at your solution. 
