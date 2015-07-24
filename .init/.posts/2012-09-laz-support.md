I have now added [LASzip][] support to [CloudAE][].  LASzip is a compression library that was developed by [Martin Isenburg][isenburg][^1] for compressing LAS points into an LAZ stream.  Using the LASzip library, an LAZ file can be decompressed transparently as if it was an LAS source.  This differs from the approach taken by [LizardTech][] for the MG4 release of [LiDAR Compressor][lidarcompressor], which does not necessarily maintain conformance to the LAS point types.  Due to the compression efficiency and compatibility of the LAZ format, it has become popular for storing archive tiles in open data services such as [OpenTopography][] and [NLSF][].

I link to the LASzip library in a similar fashion as [libLAS][], while providing a C++/CLI wrapper for operating on blocks of bytes.  As a result, I am able to pretend that the LAZ file is actually an LAS file at the byte level rather than the point level.  This allows me to support the format easily within my Source/Segment/Composite/Enumerator framework.  I merely needed to add a simple LAZ Source and StreamReader, and the magic doth happen.  There is minimal overhead with this approach, since the single extra memcpy for each point is not much compared to decompression time.

LAZ writer support is similarly straightforward, but I am sticking with LAS output for now, until I have more time to determine performance impacts.

[^1]: Thanks to Martin for his suggestions regarding implementation performance.  It turns out there is a bug in the ifstream/streambuf when compiling with CLR support.  I had to extract the stream operations into a fully native class in order to achieve the desired performance.

[laszip]: http://www.laszip.org/  "LASZip"
[liblas]: http://www.liblas.org/ "libLAS"
[isenburg]: http://www.cs.unc.edu/~isenburg/  "Martin Isenburg"
[lizardtech]: http://www.lizardtech.com/  "LizardTech"
[lidarcompressor]: http://www.lizardtech.com/products/lidar/ "LiDAR Compressor"
[opentopography]: http://www.opentopography.org  "OpenTopography"
[nlsf]: https://tiedostopalvelu.maanmittauslaitos.fi/tp/kartta?lang=en  "National Land Survey of Finland"

[cloudae]: /cloudae/  "CloudAE"
