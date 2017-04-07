FAQ
====


Why the installation requires so many DI extensions?
----

First not all of them are exactly required. The installation shows the recommended configuration. Second while I could have put everything into one giant extension most of the stuff is general and not directly related to forms so I decided to split it. Besides most of the extensions are actually very simple.


Why is Twig used internally?
----

This package provides some Latte macros which you can use, but of course they are using Twig internally. Recreating the form themes using Latte would be very complicated and is not among the goals of this package. Feel free to ask me for more details if you want to implement it yourself.

