Compiling [LASzip][] is simple, but what what does performance look like when using LASzip in a managed environment?  The first thing to realize is that accessing points individually is very expensive across a managed boundary.  That means that using an equivalent of P/Invoke individually for each point will add a substantial amount of overhead in a C# context.  To reduce the number of [interop thunks][interop] which need to occur, the most important step is to write an intermediate class in native C++ which can retrieve the individual points and return them in blocks, transforming calls from this:

~~~ {cpp}
bool LASunzipper::read(unsigned char * const * point);
~~~

...to something like this:

~~~ {cpp}
int LAZBlockReader::Read(unsigned char* buffer, int offset, int count);
~~~

The next, less important, performance consideration is to create a C++/CLI interop layer to interface with the block reader/writer.  This allows us to hide details like marshaling and pinning, and uses the C++ Interop, which provides optimal performance compared to P/Invoke.

For my situation, this is exactly what I want, since [CloudAE][] is built around chunk processing anyway.  For other situations, both the "block" transformation and the interop layer can be an annoying sort of overhead, so it should definitely be benchmarked to determine whether the thunk reduction cost is worth it.

The final factor determining the performance of LASzip is the file I/O.  In [LAStools][], Martin Isenburg uses a default `io_buffer_size` parameter that is currently 64KB.  Using a similarly appropriate buffer size is the easiest way to get reasonable performance.  Choosing an ideal buffer size is a complex topic that has no single answer, but anything from 64KB to 1MB is generally acceptable.  For those not familiar with the LASzip API, `LASunzipper` can use either a `FILE` handle or an `iostream` instance, and either of these types can use a custom buffer size.

One caveat that I mentioned in my [last post][laz-support] is that when compiling a C++/CLI project in VS 2010, the behavior of customizing iostream buffer sizes is buggy.  As a result, I ended up using a `FILE` handle and [`setvbuf()`][setvbuf].  The downside of this approach is that LAZ support in my application cannot currently use all my optimized I/O options, such as using [`FILE_FLAG_NO_BUFFERING`][buffering] when appropriate.

For an example of using the LASzip API from C++, check out the [libLAS source][liblas].


[laszip]: http://www.laszip.org/  "LASZip"
[lastools]: http://www.cs.unc.edu/~isenburg/lastools/  "LAStools"
[liblas]: http://www.liblas.org/ "libLAS"
[interop]: http://msdn.microsoft.com/en-us/library/ky8kkddw.aspx  "Performance Considerations for Interop (C++)"
[setvbuf]: http://www.cplusplus.com/reference/clibrary/cstdio/setvbuf/  "setvbuf"
[buffering]: http://msdn.microsoft.com/en-us/library/windows/desktop/cc644950.aspx  "File Buffering"

[cloudae]: /cloudae  "CloudAE"
[laz-support]: /2012/09/laz-support  "LAZ Support"
