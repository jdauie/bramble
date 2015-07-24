Years ago, in the days when I developed my PHP CMS, I decided to start a new project to give me more practice with new PHP language features.  In the end, I decided to build a parser for malformed HTML.

Lets all just take a moment to let that sink in...I built an HTML parser in PHP, in spite of the availability of modules such as libxml.  Now, it's not quite as bad as it sounds.  I did first look into the current options for markup parsing and recognized that my parser was not going to improve on any of them.  I was really just doing it for fun.

So, I jumped right in.  HTML parsing is fairly straightforward, and in the end I had a robust DOM parser that handled all manner of different malformed conditions.  Once that was complete, I continued adding functionality until eventually I had a full validating parser.  Throughout this development, I didn't spend much time thinking about performance.  So, as much as I liked it, it would never have been suitable for a production environment.

Recently, I ran across the old source code for that application and decided to port it to .NET, just out of curiosity.  I knew that I had not considered performance much during that phase of my programming career, and I wondered just how slow it was.  After converting it to C#, I benchmarked it against some current parsers, and discovered that it was abysmal.

So now it was time for a new project.  I looked into modern DOM, SAX, and Pull parsers and did not find any .NET implementations that could rival the speed of parsers such as [pugixml][], [AsmXml][], and [RapidXml][] (although in the latter case some of that speed comes from being a bit loose with the spec).  Having done a lot of .NET benchmarking for my [CloudAE][] project, I was curious how fast of a conforming HTML/XML parser I could write in C#.

Welcome the [Snail XML Parser][snail].


[pugixml]: http://pugixml.org/ "pugixml"
[asmxml]: http://tibleiz.net/asm-xml/ "AsmXml"
[rapidxml]: http://rapidxml.sourceforge.net/ "RapidXml"

[cloudae]: /cloudae/  "CloudAE"
[snail]: /snail/  "Snail XML Parser"
