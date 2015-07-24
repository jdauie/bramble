I have been on an hiatus from development recently, due to injury, but I am now starting to do some coding for a couple of hours a day to build my hand strength back up.  Since I have the time, I decided to learn about [WebGL][] and get myself up to speed with modern JavaScript libraries.  When I first developed my [CloudAE][] platform, I used WPF 3D for rendering meshes, but I found it to be extremely limited in functionality and performance.  I decided to make a WebGL viewer to replace it, starting with rendering simple LAS point clouds and tacking on functionality as I have time.  As I brought myself up-to-date, I found that developers have made an enormous number of complex and powerful WebGL demos and applications.  Although it was good to know that WebGL and HTML5 would probably be able to handle my needs, it was disconcerting to realize how out-of-date my knowledge was, due to my focus on desktop/server development.  

My first basic implementation was in straight-up WebGL, but I found that due to my hand limitations, I just wasn't adequately productive, so I learned about available JavaScript 3D libraries and eventually decided on [three.js][].  Three.js is a reasonably well-maintained library that doesn't have too many limitations for the current scope of my viewer.  Eventually, I will have to break down and go back closer to WebGL, but not just yet.  The main technical issues I have right now are the inability to do buffer interleaving properly and limitations on the number of scene nodes.  It can also be difficult to develop against the unstable three.js API, since I might be able to find two or three different solutions to a problem, but none of them are valid in the current version, which requires me to delve into the source (the [migration][] page is often inadequate).

[![](/uploads/2014/05/CloudView-TO.png){.callout =280}](/uploads/2014/05/CloudView-TO.png)
I have uploaded the [demo][] version of what I am giving the temporary (and uninteresting) name [CloudView][].  Among other things, it is a learning ground for JavaScript frameworks, [web workers][], and the HTML5 [File API][].

Next time, I will outline the iterations of my CloudAE spatialization algorithms, including the current SATR2 implementation which is being improved for optimal use by CloudView.


[file api]: http://www.w3.org/TR/FileAPI/  "File API"
[web workers]: https://developer.mozilla.org/en-US/docs/Web/Guide/Performance/Using_web_workers  "Using web workers"
[three.js]: http://threejs.org/  "three.js"
[migration]: https://github.com/mrdoob/three.js/wiki/Migration  "Three.js Migration"
[webgl]: https://en.wikipedia.org/wiki/WebGL  "WebGL"
[demo]: http://cloudview.jacere.net  "CloudView Demo"
[cloudview]: /cloudview  "CloudView"
[cloudae]: /cloudae  "CloudAE"