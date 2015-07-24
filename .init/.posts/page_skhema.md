Skhema is a templating engine with an elegant and compact notation, that I initially developed for use by [Bramble][].  This page has a basic description of the syntax, with some discussion of binding and serialization, and a few examples thrown in for good measure.  I do not anticipate changing the current language except to add compatible enhancements as I find the time.  

[bramble]: /bramble

Syntax
======

~~~ {skhema}
{@template}
~~~
	* Defines a binding source.
	* May be defined within another template.  Inline templates have no restrictions on usage.
	* May inherit another template.
	* May be included by another template.
	* Only templates are allowed at the root level.  Anything else is undefined (ignored or exception).

~~~ {skhema}
{$variable}
~~~
	* Slot to be filled either by data-binding or template inheritance.
	* If the variable is available for binding, but no value is found in the binding context, the evaluator will look in the root context before giving up.  This is the current mechanism for handling global variables.
	* Undefined behavior if the variable never gets a value (ignored or exception).
	* Filter support, which allows calling registered user-defined functions.

~~~ {skhema}
{#include}
~~~
	* Includes another named template inline.
	* The included template inherits the current binding context if a named binding is not found.
	* The generator will throw an exception if there are cycles in the dependency graph.

~~~ {skhema}
{^extend}
~~~
	* Extends another template.
	* A template can extend at most one template, and this token must be first (ignoring whitespace).
	* Variables are inherited, with any values defined in the parent.

~~~ {skhema}
{.define}
~~~
	* Defines the contents of a variable from an inherited template.
	* Variables currently have no access modifiers, so there are no private variables which cannot be defined (or redefined).
~~~ {skhema}
{?source}
~~~
	* Binding source, either for a list or just for changing the binding context.
	* Implemented as anonymous templates that are transformed into includes.
~~~ {skhema}
{/close}
~~~
	* Closes the current scope.
	* Any text after the `/` is irrelevant.
~~~ {skhema}
{%call}
~~~
	* Basic function call with only a few built-in functions available for now.
	* User-defined functions can be called just like with variable filters.


Data Binding
============

A root named binding will apply to all matching template instances.  There is currently no way to specify that a template usage cannot be bound by name.  I probably won't change this because it would be very poor design to have a template structure where that is a possibility.  Such silliness will not be allowed.

For now, binding is still very basic.  In the Bramble MVC pattern, the Model is responsible for querying the database and transforming the results into the template mappings.  The following snippets show part of the process of mapping query results to the expected template parameters and assigning them to a root named binding.

~~~ {php}
$posts_sql = '
	SELECT p.ID, p.Date, p.Title, p.Slug, p.Content, u.DisplayName FROM Posts p
	INNER JOIN Users u ON p.AuthorID = u.ID
	WHERE p.Type = 2
	AND p.Date BETWEEN :start AND :end
	ORDER BY p.Date DESC
';

$list[] = [
	'author'  => $row['DisplayName'],
	'date'    => date('Y-m-d', $time),
	'url'     => FormatURL($time, $row['Slug']),
	'title'   => $row['Title'],
	'content' => $row['Content'],
	'categories' => $categories,
];

return [
	'Posts' => [
		'title' => $title,
		'list' => $list
	]
];
~~~


Functions and Filters
=====================

This example shows a variable being processed using a registered `subvert` filter.  The Subvert renderer enables code formatting, sets a root URL for relative URL handling, and sets a header level).  Currently, Skhema does not support chaining filters together because I haven't decided if I like the syntax.

~~~ {skhema}
{$content:subvert[code,root,header=3]}
~~~

The filter needs to be registered, of course, and the arguments are simply the variable value in question, the filter options (from the brackets), if any, and the current binding context.  Any applicable filter options are the responsibility of the filter handler.

~~~ {php}
TemplateManager::RegisterFilter('subvert', function($var, $filter_options, $context) {
	// handle options
	// ...
	return Subvert::Parse($var, $options);
});
~~~

Functions work the same way, except that they get registered with a `RegisterFunction` instead, and they have no associated variable.


Serialization
=============

There are two supported serialization modes for saving the graph state.  The fastest serialization seems to be the [`serialize()`](http://php.net/manual/en/function.serialize.php) function, but serialization performance is not particularly relevent in a production scenario where templates should only rarely be recompiled.  Deserialization performance is much more important.  The following is an example of the `serialize()` output.  Performance is decent for both serialization and deserialization.  It is a good general-purpose approach if a bytecode cache is not available.

~~~ {phps}
a:23:{s:28:"__Navigation:nav-list-global";O:15:"Jacere\Template":5:{s:23:" Jace
re\Template m_name";s:28:"__Navigation:nav-list-global";s:23:" Jacere\Template 
m_root";O:11:"Jacere\Node":3:{s:10:" * m_token";O:16:"Jacere\NameToken":2:{s:24
:" Jacere\NameToken m_type";i:6;s:24:" Jacere\NameToken m_name";s:15:"nav-list-
global";}s:11:" * m_parent";N;s:10:"m_children";a:5:{i:0;s:14:"<li><a href="";i
:1;O:16:"Jacere\NameToken":2:{s:24:" Jacere\NameToken m_type";i:2;s:24:" Jacere
\NameToken m_name";s:3:"url";}i:2;s:2:"">";i:3;O:16:"Jacere\NameToken":2:{s:24:
...
~~~

The next approach is to generate PHP code to rebuild the graph.  The following is an example of the output (de-minified), which is usually about half the size of the `serialize()` output.  It takes about twice as long to serialize, and if there is no bytecode cache then it also takes about twice as long to deserialize (i.e. execute).  If a good bytecode cache is in place, then this approach provides the best deserialization performance.

~~~ {php}
'__Navigation:nav-list-global' => new Template(
	new Node(new NameToken(TokenType::T_SOURCE, 'nav-list-global'), NULL, [
		'<li><a href="',
		new NameToken(TokenType::T_VARIABLE, 'url'),
		'">',
		new NameToken(TokenType::T_VARIABLE, 'text'),
		'</a></li>'
	]), '__Navigation:nav-list-global', NULL, []),
'__Navigation:nav-list' => new Template(
	new Node(new NameToken(TokenType::T_SOURCE, 'nav-list'), NULL, [
		'<li><a href="',
		new NameToken(TokenType::T_VARIABLE, 'url'),
		'">',
		new NameToken(TokenType::T_VARIABLE, 'text'),
		'</a></li>'
	]), '__Navigation:nav-list', NULL, []),
//...
~~~

I also implemented a serialization mode using PHP's [`JsonSerializable`](http://www.php.net/manual/en/class.jsonserializable.php) interface, but it was much too slow to be included.  I could probably come up with something that performs comparably to the way I output PHP code if I generate the JSON myself, but [`json_decode()`](http://www.php.net/manual/en/function.json-decode.php) is still much slower than my other approaches.

Examples
========

This is a basic template with variables, inline template definitions, includes, and binding source.  The inline templates are probably not a common way of doing things, but they work the same as included instances.  In this particular case, they simply serve to change the named binding context.  The `{$content}` variable in this case is intended to be populated by an inheriting template.

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

Here is an example of inheriting this base template to show a list of posts.  The `{$content}` variable is defined as a binding source, which is basically a loop over the named data context.

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
			{$content:subvert[code,root]}
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

This example shows an inheritance hierarchy where each template extends or defines the functionality of the parent.  I originally added shortcut syntax for setting variables like `{.title="Recent Posts"}`, but I don't think it is any more manageable, and it isn't any shorter, so I removed that syntax.

~~~ {html skhema}
{@SidebarSection}
	<div class="right-sub-section" id="{$id}">
		<div class="title">
			<h2>{$title}</h2>
		</div>
		{$content}
	</div>
{/@}

{@SidebarSectionList}
	{^SidebarSection}
	{.content}
		<ul>
			{$list}
		</ul>
	{/.}
{/@}

{@SidebarSectionLinkList}
	{^SidebarSectionList}
	{.list}
		{?list}
			<li><a href="{$url}" title="{$text}">{$text}</a></li>
		{/?}
	{/.}
{/@}

{@SidebarSectionRecent}
	{^SidebarSectionLinkList}
	{.id}recent-posts{/}
	{.title}Recent Posts{/}
{/@}
~~~

Usage
=====

The current API requires calling the TemplateManager class, which breaks autoloading because of my file names.  I will get around to fixing that soon.

~~~ {php}
$source = $model->Evaluate();
$manager = TemplateManager::Create(TEMPLATE_PATH);
$output = $manager->Evaluate($param_template, $source);
~~~

