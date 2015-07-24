~~~ {php}
$sql = <<<"EOD"
	INSERT INTO Users (Name, Email, Password, DisplayName)
	VALUES ('jdauie', $email, :password, {$obj->display})
EOD;

$sql = <<<'EOD'
	SELECT t.ID,t.Slug FROM Terms t
	INNER JOIN TermTaxonomy x ON t.ID = x.TermID
EOD;

$array = [
	'insert' => "
		INSERT INTO Posts (AuthorID, Type, Date, MenuOrder, Title, Slug, Content)
		VALUES (1, {$const(DB_OBJ_TYPE_PAGE)}, :date, :order, :title, :slug, :content)
	",
];

$posts_sql = '
	SELECT p.ID, p.Date, p.Title, p.Slug, p.Content, u.DisplayName FROM Posts p
	INNER JOIN Users u ON p.AuthorID = u.ID
	WHERE p.Type = 2
	AND p.Date BETWEEN :start AND :end
	ORDER BY p.Date DESC
';

$merged = "<?php\nnamespace {$namespace} {\n\n{$header}\n\n{$merged}\n\n}\n?>";

$list[] = [
	'author'  => $row['DisplayName'],
	'date'    => date('Y-m-d', $time),
	'url'     => FormatURL($time, $row['Slug']),
];

$output = <<<EOT
<?php

namespace Jacere\TemplateCache {
function DeserializeCachedTemplates() {
return \Jacere\Deserialize_{$uniqueId}();
}
}

namespace Jacere {
function Deserialize_{$uniqueId}() {
return [
{$output}
];
}
}
?>
EOT;
~~~

* top level list item 1
	* second level
* top level list item 2
* top level list item 3
	* second level 1
	* second level 2
* top level list item 4
* top level list item 5
* top level list item 6
	* nested
		stuff
	* nested
	
* top level list item 7
	
	* nested
	
	* nested
	
	* nested