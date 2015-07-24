I finally found some time to finish my SATR tiling algorithm for [CloudAE][].  Unlike the previous STAR algorithms, which were built around segmented tiling, this approach uses more efficient spatial indexing to eliminate temporary files and to minimize both read and write operations.  Another difference with this approach is that increasing the allowed memory usage for the tiling process will substantially improve the performance by lowering the duplicate read multiplier and taking advantage of more sequential reads.  For instance, increasing the indexing segment size from 256 MB to 1 GB will generally reduce the tiling time by 50% on magnetic media, and 30% on an SSD.


[cloudae]: /cloudae/  "CloudAE"
