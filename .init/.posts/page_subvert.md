Subvert is a non-compliant markdown parser that I built for use in my [Bramble][] framework. I dropped Atx-style headers, adjusted the syntax for blockquotes and ordered lists, and made fencing mandatory for code blocks. Markdown implementations are fine for this purpose, but I designed this new syntax because I prefer my variant and it parses more efficiently.

I also implemented most of Markdown Extra, and I will get around to finishing it soon.

Inspiration from the original [markdown][] (John Gruber), [PHP Markdown][] (Michel Fortin), [parsedown][] (Emanuil Rusev), [Simple Markup][] (Mauricio Fernandez), and many others.


[bramble]: /bramble
[markdown]: http://daringfireball.net/projects/markdown/
[php markdown]: http://michelf.ca/projects/php-markdown/
[parsedown]: http://parsedown.org/
[simple markup]: http://github.com/MFP/OcsiBlog/blob/master/simple_markup.ml