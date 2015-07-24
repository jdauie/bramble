[![](/uploads/2012/02/app_3d_78m.png){.callout =220}](/uploads/2012/02/app_3d_78m.png)
The [Cloud Analysis Engine][cloudae] is a high-performance test framework that I developed for working on LIDAR extraction algorithms.  Originally intended as a simple processing platform, the concept grew in scope as I used the project as an opportunity to explore high-performance computing in .NET and to learn about more technologies, namely WPF (including WPF 3D).

Currently, the framework supports loading and tiling LAS, LAZ, and XYZ files into a new spatialized LAS file.  This approach differs from that taken by [Martin Isenburg][isenburg] in his [LAStools][], which are based on not rearranging points, but rather indexing and using streaming processing to be able to handle large data files.  My choice to tile the file instead of using a streaming approach is based on the requirements of the algorithms that I am developing.

[![](/uploads/2012/02/app_2d_sfp.png){.callout =220}](/uploads/2012/02/app_2d_sfp.png)
The application has a table-of-contents, input file preview, event log, and 2D viewer and 3D viewer.  The UI supports color ramps, distinct color mapping, and 3D trackball navigation.  The 3D viewer is a simple WPF 3D implementation, which suffers from the feature and performance limitations inherent to that choice.  However, the code for getting WPF to render the geometry I have so far is so simple that it is not a substantial investment.  Much of my 3D code was derived from [3D Tools][3d-tools] project, making things even more simple.  The primary limitation of WPF 3D is the lack of point visualization support and the lack of vertex shading.  The former requires me to visualize point clouds with meshes and the latter requires textures for all color mappings.

Internally, the application supports custom buffer management, point-cloud tiling, dynamic tile loading mechanisms, point-cloud and compression.  Currently supported outputs are large rasters, simple previews, and both grid and delaunay mesh geometry.  I have implemented some basic utilities such as edge detection and segmentation for use by extraction algorithms.  In addition, the 2D viewer supports loading point-cloud tile regions on-the-fly as the mouse cursor moves across the image, allowing a subjective test of dynamic tile loading performance.  The 3D viewer has a similar capability, loading higher LOD meshes for regions near the cursor.

The application delivers excellent performance on large point clouds, but it no longer is blazing fast on medium-sized files because it bypasses the windows file cache, thus losing out on potential speed gains.  However, I chose to optimize for the case of massive files, because windows handles large files so poorly that many applications processing such files slow to a glacial speed when Windows has filled all available memory with cache and begins to thrash.  Thus far, the tiling mechanism has gone through three major performance overhauls, so I won&#8217;t go into too much detail about performance metrics here, since they will probably change again.


[isenburg]: http://www.cs.unc.edu/~isenburg/  "Martin Isenburg"
[lastools]: http://www.cs.unc.edu/~isenburg/lastools/  "LAStools"
[3d-tools]: http://3dtools.codeplex.com/  "3D Tools"

[cloudae]: /cloudae/  "CloudAE"
