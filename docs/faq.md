FAQ
====

Why the installation requires so many DI extensions?
----

First not all of them are exactly required. The installation shows the recommended configuration. Second while I could have put everything into one giant extension most of the stuff is general and not directly related to forms so I decided to split it. Besides most of the extensions are actually very simple.

Why Twig instead of Latte?
----

Actually I've tried to use Latte to render the forms. Unfortunately Latte has proven to be extremely inadequate for this. Until v2.4 Latte lacked an API to load a whole template and then render just one of its blocks which is essential for rendering of symfony forms. Also the default themes in Symfony contain many advanced cases that would be difficult to solve in Latte.

If you want to try implementing it yourself I'll be happy to give you some advice about where to begin and later have a look at your solution.
