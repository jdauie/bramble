[![](/uploads/2014/05/CloudView-Hfx.png){.callout =280}](/uploads/2014/05/CloudView-Hfx.png)
When I started building the [CloudView][] LAS viewer, I decided to take advantage of it by updating my [CloudAE][] spatialization algorithm to optimize for fast 3D display without slowing down the processing pipeline.  The SATR2 algorithm is now live, and I decided to write up some of the milestones in the development of the CloudAE spatialization.  My first algorithm was mostly just a proof-of-concept, similar to something like Isenburg's [lastile][], but reorganizing the tiles into a single file rather than splitting into separate files.  At the present time, my spatialization is extremely fast, and built around a robust indexing framework.  

The next set of improvements will involve improved dynamic code generation for filtering and converting points.  I am also considering additional changes relating to 3D visualization, such as additional sorting in the SATR2 algorithm or reworking the spatialization to handle general point clouds instead of just focusing on airborne LiDAR.

This list also leaves out the numerous unrelated improvements that may have been developed in parallel with these iterations, such as LAS 1.4 output support, composite readers, unbuffered I/O, and fast allocation.  Figuring which development phase all the individual features belong to would require a tedious inspection of my source control history, and that's just not worth it.


SOOT
==========

Compute a tile grid based on the assumption that the points are evenly distributed across the extent.  Traverse the file to get the tile counts.  Allocate a number of fixed-size buffers and traverse the file again to do the tiling.  For each point that doesn't have an active tile buffer, acquire a free buffer and associate it with that tile.  If the new point fills a buffer, flush the buffer to the correct location in the file.  If there are no free buffers, flush one (last used or most full).  When the traversal is complete, flush remaining buffers.  As I said, this first attempt is similar to tiling to separate files, except that the separate file approach does not require the initial tile counts because it pushes the buffering problem off to the OS and filesystem, resulting in file fragmentation and varying performance.

 * Simple algorithm.
 * No temporary files.
 * Random write performance is dreadful (especially on magnetic media).
 * Sparse points will result in more points per tile than intended.


STAR
==========

Traverse the file to get estimated tile counts.  Use estimated counts to create new tile boundaries (taking into account empty space).  Break point data into logical segments which can each fit in a large buffer.  Tile each segment individually, but use the same tile boundaries for each (so each segment fills a subset of the counts).  For each segment, read the points into memory, count and sort them, and write out the tiled segment.  Merge the tiled segments together.

 * Faster than SOOT.
 * Resulting points per tile are more accurate on sparse points.
 * Requires simultaneous open read handles on all intermediate files.
 * Uses 1x the output size for temporary files.

STAR2
=========

During the final merge operation, calculate how much data could be loaded from each intermediate file to fill the large buffer and read much larger chunks before flushing the large buffer to the output.

 * Much faster merge than STAR due to larger sequential operations.
 * Does not require intermediate read handles to stay open.
 * Temporary file usage is the same.
 * More complex interleaving logic.
 * Increased memory usage during merge, but since it reuses the large buffer, no overall increase.

SATR
==========

Traverse the file to create high resolution index.  Create tile boundaries from index, but skip populating them with actual counts.  Create sparse logical segments from the index data such that they can efficiently read the tiles in the order that they will be written.  For each segment, read the sparse file data into the large buffer in an optimized sequence, filtering out the extra points that do not belong in the associated tiles, and sort the remaining points into tile order.  Append the buffer directly to the output file.

 * Faster than STAR2 due to halving the data written to disk.
 * No temporary files.
 * Depending on the point order and the buffer size, the sparse read multiplier might be 1.1-1.8x, and since it is sparse, it is similar to the previous 2x sequential read from STAR2.
 * The spatial indexes can be saved either for regenerating the spatialized file without the initial pass, or for direct use instead of spatializing (when disk space is a concern).

SATR2
==========

During the segment sort operation, extract representative low-res grid points from each tile and append them to a common buffer.  Medium-res points can be shifted to the beginning of each tile.  After the segments have been written, append the common buffer with the low-res points to the end of the file so they can all be read at once.  Retrieving tile points now requires pulling the subset of associated low-res points from the common buffer and adding them to the remaining points in the tile location.  As part of the emphasis on rendering, grids now use square cells for all tile calculations.  

 * Low-resolution points are available immediately in a sequential region.
 * Negligible performance difference for tile operations.
 * Reading the tiles is slightly more complicated internally, but it is hidden by the API.
 * Tile metadata is increased by the addition of a low-res count.
 * Points are "out-of-order", but since they are already in an artificial order, it hardly matters.
 * Slightly slower spatialization than SATR1 due to extra point shifting (it only becomes measurable when using low-res point criteria such as nearest to grid cell center).
 * More complex and time-consuming to debug.



[lastile]: http://rapidlasso.com/lastools/lastile/  "lastile"

[cloudview]: /cloudview/  "CloudView"
[cloudae]: /cloudae/  "CloudAE"