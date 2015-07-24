[![][78m_stitching1-img]{.callout =220}][78m_stitching1]
I have completed a basic tile stitching algorithm for use in completing the 3D mesh view.  At this point, it only stitches equal resolutions, so it will stitch low-res tiles together and high-res tiles together, but it will leave a visible seam between tiles of different resolutions.  Obviously, that is not a difficult problem, since I am only stitching regular meshes at this point.  However, I do plan to get irregular mesh support eventually.  Currently, I have an incremental Delaunay implementation, but the C# version is much slower than the original C++, to the extent that it is not worth running.  The [S-hull][] implementation that I downloaded is much faster, but does not produce correct results.

The primary limitation of the tile-based meshing and stitching is that WPF 3D has performance limitations regarding the number of [MeshGeometry3D][] instances in the scene.  For small to medium-sized files, there may be up to a few thousand tiles with the current tile-sizing algorithms.  WPF 3D performance degrades substantially when the number of mesh instances gets to be approximately 12,000.  Massive files may have far more tiles than that, and adding mesh instances for the stitching components makes the problem even worse.  I have some ideas for workarounds, but I have not yet decided if they are worth implementing since there are already so many limitations with WPF 3D and this 3D preview was never intended to be a real viewer.

![][78m_stitching3-img]


[s-hull]: http://www.s-hull.org/  "S-hull"
[meshgeometry3d]: http://msdn.microsoft.com/en-us/library/system.windows.media.media3d.meshgeometry3d.aspx  "MeshGeometry3D Class"

[78m_stitching1]: /uploads/2012/02/78m_stitching1.png  "78m_stitching1"

[78m_stitching1-img]: /uploads/2012/02/78m_stitching1.png  "78m_stitching1"
[78m_stitching3-img]: /uploads/2012/02/78m_stitching3.png  "78m_stitching3"