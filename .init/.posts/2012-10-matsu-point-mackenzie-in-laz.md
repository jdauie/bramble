The [Matanuska-Susitna Borough's 2011 LiDAR & Imagery Project][matsu] has been around for a while now, but I recently discovered that the [Point MacKenzie][point-mackenzie-laz] data on the public mirror has been made available as compressed LAZ.  The LASzip format is not ideal for all purposes, but it is always a major improvement when data servers provide data in LAZ format because of the massive reduction in bandwidth and download times.

Even when [CloudAE][] did not support LAZ directly, it was always well worth it to download the compressed data and [convert][lastools] it to LAS.  Now that [CloudAE supports LAZ][laz-support], everything is much simpler.


[lastools]: http://www.cs.unc.edu/~isenburg/lastools/ "LAStools"
[matsu]: http://matsu.gina.alaska.edu/ "Matanuska-Susitna Borough's 2011 LiDAR & Imagery Project"
[point-mackenzie-laz]: http://matsu.gina.alaska.edu/LiDAR/Point_MacKenzie/Point_Cloud/Classified.laz/

[cloudae]: /cloudae/ "CloudAE"
[laz-support]: /2012/09/laz-support/ "LAZ Support"
