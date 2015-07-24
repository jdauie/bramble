The initial support for functions in Skhema was more for test purposes than anything else.  I haven't had any need for functions in my current framework, but I imagine that I probably will at some point.  I made sure that they fit into the language design that I was finalizing, but I left the implementation as an inefficient late-evaluation.  

I had initially thought that the best approach would be to compile functions during the graph-generation phase, but I have decided instead to compile functions during the evaluation phase and just do a proper lazy evaluation.  Compiling during graph-generation would unnecessarily complicate the serialization process with proxy objects that don't improve the situation.  A more serious issue is that the graph generator doesn't necessarily know if a particular function will be legal during evaluation.  This is an unfortunate side effect of the way I change context scope for data-binding.  I will continue to think about this issue.

Here is a contrived example showing the most basic functions: `{%iteration}` and `{%cycle}`.  I say "contrived" because this can be done with CSS3 or any JavaScript library ever.  Technically, the iteration index doesn't need to be a function, but I like the syntax for it.  As a current implementation detail, `{%iteration}` can be written instead as `{$__iteration}`, but I wouldn't be surprised if that changed.

~~~ {html skhema}
<table>
	{?list}
		<tr style="background-color:{%cycle=white,LightGray}">
			<td>{%iteration}</td>
			<td>{$url}</td>
		</tr>
	{/}
</table>
~~~

I am also still trying to figure out the syntax I want to use for filters, so that I can move logic like markdown rendering from the model to the view, which is more appropriate.

One of the next things I will work on is a proper page cache.  Right now, full page renders cost 20-60 ms (depending on the page).  That's a bit much even for this slow Dreamhost shared server with no bytecode caching.  Almost all of the time is the database queries, so a simple cache should get me back below 20 ms for all pages.