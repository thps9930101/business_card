/**
 * Loads THREE Textures with progress events
 * @augments THREE.TextureLoader
 */
THREE.AjaxTextureLoader = function() {
    /**
     * Three's texture loader doesn't support onProgress events, because it uses image tags under the hood.
     *
     * A relatively simple workaround is to AJAX the file into the cache with a FileLoader, create an image from the Blob,
     * then extract that into a texture with a separate TextureLoader call.
     *
     * The cache is in memory, so this will work even if the server doesn't return a cache-control header.
     */

    const cache = THREE.Cache;

    // Turn on shared caching for FileLoader, ImageLoader and TextureLoader
    cache.enabled = true;

    const textureLoader = new THREE.TextureLoader();
    const fileLoader = new THREE.FileLoader();
    fileLoader.setResponseType('blob');

    function load(url, onLoad, onProgress, onError) {
        fileLoader.load(url, cacheImage, onProgress, onError);

        /**
         * The cache is currently storing a Blob, but we need to cast it to an Image
         * or else it won't work as a texture. TextureLoader won't do this automatically.
         */
        function cacheImage(blob) {
            // ObjectURLs should be released as soon as is safe, to free memory
            const objUrl = URL.createObjectURL(blob);
            const image = document.createElementNS('http://www.w3.org/1999/xhtml', 'img');

            image.onload = ()=> {
                cache.add(url, image);
                URL.revokeObjectURL(objUrl);
                document.body.removeChild(image);
                loadImageAsTexture(blob);
            };

            image.src = objUrl;
            image.style.visibility = 'hidden';
            document.body.appendChild(image);
        }

        function loadImageAsTexture(blob) {
			var me = textureLoader.load(url, /*onLoad*/ function() {
				onLoad(me, blob);
			}, ()=> {}, onError);
        }
    }

    return Object.assign({}, textureLoader, {load});
};

//module.exports = AjaxTextureLoader;