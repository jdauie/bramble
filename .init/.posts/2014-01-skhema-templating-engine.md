In my last post, I wrote about my [new templating engine](/2013/09/reinventing-the-wheel).  As I mentioned, the main unknown at the time was whether the language would adequately cover my use-cases.  Since then, I have integrated it into the MVC framework of the [Bramble Web Application Framework][bramble], and I am very satisfied with the results.  As part of the integration, I changed the project name from templ@te to [Skhema][], since the old name was not web-friendly.  This site is now running on Bramble using Skhema for rendering models.

The language has not changed much since last year.  My concern was mostly with making sure that it was a good design, but I did find time for a few things.  I improved the deserialization performance and provided alternate serialization modes.  I improved the graph evaluation performance, and added preliminary function support.  Functions are my primary extension-point at the moment, but they aren't particularly fast yet because the graph does an inefficient late-evaluation.  All I have at this point are some basic operations like iteration index and cycle, but once I get back to this project I will properly generate the graph nodes for functions and add support for more powerful operations and user extensions.

~~~ {html skhema}
<table>
	{?list}
		<tr class="{%cycle=odd,even}">
			<td>{%iteration}</td>
			<td><a href="{$url}">{$name}</a></td>
		</tr>
	{/}
</table>
~~~

For now, functions are scope-limited.  I haven't decided on a syntax yet for accessing the functions of the `outer` list from the following example.

~~~ {html skhema}
{?outer}
	{?inner}
		{%iteration}<br>
	{/}
{/}
~~~

I didn't cover this in my last post, but one of the main differences that Bramble has from many other PHP templating engines is that mine does not compile to PHP statements, but rather to a graph that is evaluated using the data binding source.  Originally, I had intended the graph/tree to be an intermediate structure that would get converted to PHP code for evaluation.  Surprisingly, however, the graph evaluation proved to be faster than the alternative template engines that I was using for comparison, so I decided to simply serialize the graph instead.


[bramble]: /bramble  "Bramble"
[skhema]: /skhema  "Skhema"