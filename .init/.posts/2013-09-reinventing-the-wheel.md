I haven't posted about development for months, but I have been getting some work done.  My current project is a new templating engine that I am preliminarily calling templ@te.  I finalized the first draft of the template language recently and just got around to implementing a parser/evaluator in PHP.

So, why on earth would I create another templating language?  There are plenty of options out there like [Twig][], [Smarty][], [Jinja2][], [Cheetah][], [Genshi][], [Django][], [Mako][], [Myghty][], [ctemplate][], and many more.  The short answer, as with most of my projects, is that I work on whatever grabs my interest.  The long answer is that while I like many of the templating languages out there (my favorites being Twig, Smarty, Django, and Jinja2), I wanted something that combined the most useful capabilities of those languages in a more elegant and compact notation with a focus on the particular style of data binding that I want.  I have met this goal so far, but it will take some time for me to be sure that the language can be extended to all the uses that I will have for it.  Beyond that, I have no idea yet whether this language will be general-purpose enough to compete with the other options that are available.

The performance of my first revision is reasonable, considering that I wrote it in PHP.  I designed it to provide a good mix of flexibility and performance, so it beats most other templating engines, but not by much.  In lieu of a full feature list (since I am still adding functionality), I will just provide an example to demonstrate the syntax.  Here is a basic template with variables, in-line template definitions, includes, and binding blocks.

~~~ {html skhema}
{@TemplateBase}
<!DOCTYPE html>
<html>
<head>
	<title>{$title}</title>
</head>
<body>
	<div id="header">
		{@Header}
			<h1><a href="{$root-url}">jacere.net</a></h1>
			{@Navigation}
				<div id="nav">
					<ul>
						{?nav-list}
							<li><a href="{$url}">{$text}</a></li>
						{/?}
					</ul>
				</div>
			{/@}
		{/@}
	</div>

	<div id="content">
		{$content}
	</div>
	<div id="sidebar">
		{#Sidebar}
	</div>
</body>
</html>
{/@}
~~~

Here is an example of inheriting this base template to show a list of posts.

~~~ {html skhema}
{@Posts}
	{^TemplateBase}
	{.content}
		{?list}
			{#PostSection}
		{/?}
	{/.}
{/@}

{@PostSection}
<div class="article">
	<div class="title">
		<small>Posted by: <strong>{$author}</strong> | {$date}</small>
		<h2><a href="{$url}">{$title}</a></h2>
	</div>
	<div class="post">
		<div class="entry">
			{$content}
		</div>
		<div class="postinfo">
			Posted in
			<ul class="taglist">
				{?categories}
					<li><a href="{$url}">{$name}</a></li>
				{/?}
			</ul>
		</div>
	</div>
</div>
{/@}
~~~

The block closing syntax that I am using is mostly for style reasons.  The following closing formats are parsed identically.

~~~ {skhema}
{/}
{/@}
{/name}
~~~

Similarly, the parser accepts alternate delimiters, so the individual brace format can be replaced as desired (e.g. `{$var}` could be `<$var>` or `{{$var}}`).

As for using the template, right now I am simply binding to nested PHP arrays.  I have not yet decided where I want to go from here.

~~~ {php}
$context = [
	'Posts' => [
		'title'	=> 'templ@te',
		'list'	 => [
			[
				'author'  => 'Joshua Morey',
				'date'	=> '2013-03-14',
				'url'	 => '/2013/03/satr-development-finalized/',
				'title'   => 'SATR Development Finalized',
				'content' => '<p>I finally found some time...</p>',
				'categories' => [
					['url' => '/category/c/', 'name' => 'C#'],
					['url' => '/category/cloudae/', 'name' => 'CloudAE'],
					['url' => '/category/lidar/', 'name' => 'LiDAR'],
				],
			],
			[
				'author'  => 'Joshua Morey',
				'date'	=> '2011-12-23',
				'url'	 => '/2011/12/tile-stitching/',
				'title'   => 'Tile Stitching',
				'content' => '<p>I have completed...</p>',
				'categories' => [
					['url' => '/category/cloudae/', 'name' => 'CloudAE'],
				],
			],
		]
	],
	'Header' => [
		'root-url' => '/template/parse.php',
	],
	'Navigation' => [
		'nav-list' => [
			['url' => '/about', 'text' => 'Contact'],
			['url' => '/contact', 'text' => 'About'],
		],
	],
];

$output = TemplateManager::Evaluate('Posts', $context);
~~~

There are many things I plan to add/improve, such as filters (e.g. escaping), compacted syntax combinations, more versatile named bindings, and improved caching performance.  Eventually, I will also choose a name for the project, since "templ@te" isn't very web-friendly.

[twig]: http://twig.sensiolabs.org/  "Twig"
[smarty]: http://www.smarty.net/  "Smarty"
[jinja2]: http://jinja.pocoo.org/  "Jinja2"
[cheetah]: http://www.cheetahtemplate.org/  "Cheetah"
[genshi]: http://genshi.edgewall.org/  "Genshi"
[django]: https://www.djangoproject.com/  "Django"
[mako]: http://www.makotemplates.org/  "Mako"
[myghty]: http://www.myghty.org/  "Myghty"
[ctemplate]: http://code.google.com/p/ctemplate/  "ctemplate"
