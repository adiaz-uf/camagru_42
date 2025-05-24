document.addEventListener('DOMContentLoaded', () => {
    const container = document.querySelector('.thumbnail-container');
    const popup = document.getElementById('postPopup');
    const popupImage = document.getElementById('popupImage');
    const captionInput = document.getElementById('popupCaption');
    const submitBtn = document.getElementById('submitPopupBtn');

    container.addEventListener('click', (e) => {
        if (e.target.tagName === 'IMG' && e.target.classList.contains('thumbnail-img')) {
            popupImage.src = e.target.src;
            popup.style.display = 'flex';
        }
    });

     submitBtn.addEventListener('click', async () => {
        const imageUrl = popupImage.src;
        const caption = captionInput.value;

        if (!imageUrl) {
            alert("No image selected.");
            return;
        }

        try {
            const response = await fetch(`${window.location.origin}/backend/app/post_img.php`, {
                method: 'POST',
                credentials: 'include',
                body: JSON.stringify({ image: imageUrl, caption })
            });

            const result = await response.json();

            if (result.success) {
                alert('Image posted successfully!');
                popup.style.display = 'none';
                captionInput.value = '';
            } else {
                alert('Error: ' + result.message);
            }
        } catch (err) {
            console.error(err);
            alert('Error posting image.');
        }
    });
});