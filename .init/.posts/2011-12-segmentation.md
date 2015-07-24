[![](/uploads/2011/12/toronto_segment.png){.callout =220}](/uploads/2011/12/toronto_segment.png)
Since the framework performance has reached the goals that I set for myself, I have paused work on tiling optimizations and moved on to algorithm development.  The first step was to build a raster format data source.  I decided to go with a non-interleaved tiled binary format, which fits well with my intended usage.  In the future, I will make a more robust determination of my needs with regard to raster support.

One of the first high-performance algorithms that I developed in years past was a basic region-grow.  I have now implemented my tile-based segmentation algorithm (region-growing) for 2D analysis.  This allows me to perform segmentation on an interpolated cloud or raster, using the tiles to allow a low memory footprint.

Soon, I will need to make a vector output source, possibly SHP.  That will give me better geometry to visualize than the textured meshes I am currently using in the 3D viewer.
