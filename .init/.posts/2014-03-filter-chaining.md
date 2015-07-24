Not long after my last post, I updated the [Skhema][] graph generation to support filter chaining.
Functions and variables are now implemented as a new type of evaluation token with a stack of filters, using a shared syntax.
The following examples show basic function usage.
~~~ {skhema}
{%cycle[white,LightGray]}
{%format-url[post]}
~~~
The `cycle` function is one of the built-in functions that I described previously.  The only change is that the syntax has been tweaked to use bracketed options.
The `format-url` function is one of the user-defined functions used by [Bramble][] to generate a URL from a data context.  In this case the `post` option requires that context has both a `'time'` and `'slug'` variable which can be transformed into a URL such as `/2014/01/skhema-filters`.  It's somewhat opaque this way, since just looking at the template does not necessarily provide enough information to know which variables the model needs to provide, but I think that encapsulating this functionality in user-defined functions is better in the long run.

~~~ {skhema}
{$content:subvert[code,root]}
{$time:format-date}
{%first[list/time]:format-date[atom]}
~~~
The filter syntax has not changed, but I updated the implementation so that filters can be stacked on variables, functions, and other filters.  [Bramble][] has enough functionality at this point to give me an assurance that the current approach should work well.


[bramble]: /bramble  "Bramble"
[skhema]: /skhema  "Skhema"