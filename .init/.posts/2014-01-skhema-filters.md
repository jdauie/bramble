I finally decided on a syntax, so [Skhema](/skhema) now has filter support.  As a result, I have updated [Bramble](/bramble) by moving the markdown parser references from models to views (where they always belonged).  Skhema templates can now use filters on variables like `{$var:filter[key1,key2=val]}`, where brackets contain the optional filter parameters.  This is shown in the following template that Bramble uses during the rendering of this post.

~~~ {html skhema}
{@PageSection}
<div class="article">
	<div class="title">
		<h2><a href="{$url}">{$title}</a></h2>
	</div>
	<div class="post">
		<div class="entry">
			{$content:subvert[code,root,header=3]}
		</div>
	</div>
</div>
{/@}
~~~

The `subvert` filter handler needs to be registered with the `TemplateManager`, and any applicable filter options are the responsibility of the filter handler.  From this example template, the Subvert renderer enables code formatting, sets a root URL for relative URL handling, and sets the current header level at `h3` (because the page design already uses `h1` and `h2`).

~~~ {php}
TemplateManager::RegisterFilter('subvert', function($var, $filter_options, $context) {
	// handle options
	// ...
	return Subvert::Parse($var, $options);
});
~~~

I added user-defined function handlers, in addition to filters, because they use the same mechanism.  I just don't have any use-cases for them at this time.