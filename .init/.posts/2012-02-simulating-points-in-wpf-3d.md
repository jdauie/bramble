[![](/uploads/2012/02/cloud_terrestrial_test1.png "cloud_terrestrial_test1"){.callout =220}](/uploads/2012/02/cloud_terrestrial_test1.png)
I have implemented a simple 3D point cloud visualization control intended to test whether WPF 3D can be reasonably used as a point cloud viewer.  Up to this point, I have been rendering clouds using [TIN][] geometry because WPF only supports meshes.

Using indexed meshes, I generated geometry that could represent points in 3D space.  At first, I tried to make the points visible from all angles, but that required too much memory from the 3D viewer even for simple clouds.  I reduced the memory footprint by only generating geometry for the top face of the pseudo-points, which allowed small clouds to be rendered.  For large clouds, I had to thin down to fewer than 1m points before generating geometry.  Even at this point, I was forced to make the point geometry larger than desired in order for it to not disappear when rendered.

The conclusion from this test is that WPF 3D is totally unsuitable for any type of large-scale 3D point rendering.  Eventually, I will need to move to [OpenGL][], [Direct3D][], or some API such as [OSG][] or [XNA][].


[tin]: http://en.wikipedia.org/wiki/Triangulated_irregular_network "Triangulated Irregular Network"
[opengl]: http://en.wikipedia.org/wiki/OpenGL "OpenGL"
[direct3d]: http://en.wikipedia.org/wiki/Microsoft_Direct3D "Direct3D"
[osg]: http://en.wikipedia.org/wiki/OpenSceneGraph "OpenSceneGraph"
[xna]: http://en.wikipedia.org/wiki/Microsoft_XNA "Microsoft XNA"
