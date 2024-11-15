document.addEventListener('DOMContentLoaded', function() {
    const backgroundSlideshow = document.getElementById('backgroundSlideshow');
    const images = window.ffArtworks;
    
    console.log('Background Slideshow Element:', backgroundSlideshow);
    console.log('Available Images:', images);
    
    if (!images || images.length === 0) {
        console.warn('No images available for slideshow');
        return;
    }

    let currentImageIndex = 0;
    
    function changeBackground() {
        const nextImage = images[(currentImageIndex + 1) % images.length];
        console.log('Changing to image:', nextImage);
        
        const tempImage = new Image();
        
        tempImage.onload = function() {
            backgroundSlideshow.style.backgroundImage = `url("${nextImage}")`;
            currentImageIndex = (currentImageIndex + 1) % images.length;
            console.log('Background changed successfully');
        };
        
        tempImage.onerror = function() {
            console.error('Failed to load image:', nextImage);
        };
        
        tempImage.src = nextImage;
    }
    backgroundSlideshow.style.backgroundImage = `url("${images[0]}")`;
    if (images.length > 1) {
        setInterval(changeBackground, 4000);
    }
});