In my last post, I referenced the block-based nature of point cloud handling in [CloudAE][].  The following example shows the basic format for enumerating over point clouds using the framework.  At this level, the point source could be a text file, an LAS or LAZ file, or a composite of many individual files of the supported types.  The enumeration hides all such details from the consumer.

~~~ {csharp}
using (var process = progressManager.StartProcess("ChunkProcess"))
{
	foreach (var chunk in source.GetBlockEnumerator(process))
	{
		byte* pb = chunk.PointDataPtr;
		while (pb < chunk.PointDataEndPtr)
		{
			SQuantizedPoint3D* p = (SQuantizedPoint3D*)pb;
			
			// evaluate point
			
			pb += chunk.PointSizeBytes;
		}
	}
}
~~~

This can be simplified even more by factoring the chunk handling into IChunkProcess instances, which can encapsulate analysis, conversion, or filtering operations.

~~~ {csharp}
var chunkProcesses = new ChunkProcessSet(
	quantizationConverter,
	tileFilter,
	segmentBuffer
);
 
using (var process = progressManager.StartProcess("ChunkProcessSet"))
{
	foreach (var chunk in source.GetBlockEnumerator(process))
		chunkProcesses.Process(chunk);
}
~~~

The chunk enumerators handle progress reporting and checking for cancellation messages.  In addition, they hide any source implementation details, transparently reading from whatever IStreamReader is implemented for the underlying sequential sources.


[cloudae]: /cloudae/  "CloudAE"
