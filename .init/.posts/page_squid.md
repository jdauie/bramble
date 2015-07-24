[![](/uploads/2011/12/squid_channel.png){.callout =300}](/uploads/2011/12/squid_channel.png)
Squid Download Manager is a simple download application, intended to be part of a larger idea that I got bored with.  In it's current state, it supports normal downloads, channels and video sources, with the ability to browse through videos and easily download desired resolutions.

From a code perspective, basic features include fast multi-threaded downloads, WPF, and dynamic plugin loading.  I used the project as an opportunity to get back up-to-date with VS2010 and .NET 4.0, including data binding, WPF, LINQ, and parallel tasks.

[![Squid.Core Design](/uploads/2011/12/SquidDesignFull.png){=100%}](/uploads/2011/12/SquidDesignFull.png)

I have written channel and video source implementations for Youtube and Blip.tv, but they get out of date as the websites are updated, and I have not updated the parser plugins frequently.  Many other download tools already do a fine job of parsing video sites, and I won't be keeping this tool up-to-date unless I decide to make the larger tool that it is a part of.

