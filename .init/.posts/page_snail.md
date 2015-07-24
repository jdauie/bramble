Snail is an attempt to build the fastest and most memory-efficient C# XML parser possible.  For comparable C++ projects, see [pugixml][], [AsmXml][], [RapidXml][], [VTD-XML][].

Snail is an efficient pull parser as well as a DOM generator, with extremely fast sequential and random navigation.  I also seem to have unintentionally re-implemented many of the underlying mechanisms of VTD-XML, such as token descriptors and location caches.

I am still working on improving the performance of attribute parsing, DOM generation, and indexing.  At this point, I am not doing much testing on files larger than 100MB, and I still have a number of features and API improvements to implement.

Current pull-parser performance:

420M characters/s<br/>5.4 cycles/character

Testing files include:

* [XMark](http://www.xml-benchmark.org/)
* [KJV (usfx, osis)](http://www.ebible.org/kjv/)
* [GCIDE_XML](http://www.ibiblio.org/webster/)
* [pugixml](http://pugixml.org/benchmark/)
* [religion.200](http://www.ibiblio.org/pub/sun-info/standards/xml/eg)
* XHTML
* VCPROJ
* SOAP

[pugixml]: http://pugixml.org/ "pugixml"
[asmxml]: http://tibleiz.net/asm-xml/ "AsmXml"
[rapidxml]: http://rapidxml.sourceforge.net/ "RapidXml"
[vtd-xml]: http://vtd-xml.sourceforge.net/ "VTD-XML"
