const imageInput = document.getElementById('imageInput');
const webcamInput = document.getElementById('webcamInput');
const mainImage = document.getElementById('mainImage');
const overlayImage = document.getElementById('overlayImage');

document.querySelectorAll('.side-container img').forEach(img => {
    img.addEventListener('click', () => {
        if (mainImage.src) {
            overlayImage.src = img.src;
            overlayImage.style.display = 'block';
            imageInput.disabled = false;
            document.querySelector('.upload-label').classList.remove('disabled');
            document.querySelector('.upload-webcam').classList.remove('disabled');
        } else {
            alert("Select an image");
        }
    });
});

let isDragging = false;
let offsetX, offsetY;

const overlay = document.getElementById('overlayImage');
const editorArea = document.getElementById('imageEditorArea');

function getEventCoords(e) {
    const rect = editorArea.getBoundingClientRect();
    if (e.touches) {
        return {
            x: e.touches[0].clientX - rect.left,
            y: e.touches[0].clientY - rect.top
        };
    } else {
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
    }
}

// drag
function startDrag(e) {
    isDragging = true;
    const coords = getEventCoords(e);
    const overlayRect = overlay.getBoundingClientRect();
    offsetX = coords.x - (overlayRect.left - editorArea.getBoundingClientRect().left);
    offsetY = coords.y - (overlayRect.top - editorArea.getBoundingClientRect().top);
}

function stopDrag() {
    isDragging = false;
}

// Move sticker on drag
function moveDrag(e) {
    if (isDragging) {
        const coords = getEventCoords(e);
        let x = coords.x - offsetX;
        let y = coords.y - offsetY;

        const rect = editorArea.getBoundingClientRect();
        
        x = Math.max(0, Math.min(x, rect.width - overlay.offsetWidth));
        y = Math.max(0, Math.min(y, rect.height - overlay.offsetHeight));

        overlay.style.left = `${x}px`;
        overlay.style.top = `${y}px`;
    }
}

// Mouse
overlay.addEventListener('mousedown', startDrag);
document.addEventListener('mousemove', moveDrag);
document.addEventListener('mouseup', stopDrag);

// Touch (Mobile)
overlay.addEventListener('touchstart', startDrag);
document.addEventListener('touchmove', moveDrag);
document.addEventListener('touchend', stopDrag);

document.addEventListener('DOMContentLoaded', function () {
  const postButton = document.querySelector('.upload-btn');
  const responseMessage = document.getElementById('responseMessage');

  postButton.addEventListener('click', async function (e) {
    e.preventDefault();

    if (!imageInput.files.length || !overlayImage.src) {
      alert('Select an image and sticker.');
      return;
    }
    const realImage = document.querySelector('.uploaded-img') || mainImage;
    if (!realImage || !realImage.complete || realImage.naturalWidth === 0) {
        alert("Wait to load image completly");
        return;
    }

    const overlayRect = overlayImage.getBoundingClientRect();
    const imageRect = realImage.getBoundingClientRect();

    const relativeLeft = overlayRect.left - imageRect.left;
    const relativeTop = overlayRect.top - imageRect.top;

    const scaleX = realImage.naturalWidth / realImage.offsetWidth;
    const scaleY = realImage.naturalHeight / realImage.offsetHeight;

    const adjustedPosX = Math.round(relativeLeft * scaleX);
    const adjustedPosY = Math.round(relativeTop * scaleY);

    const scaledStickerWidth = Math.round(overlayImage.offsetWidth * scaleX);
    const scaledStickerHeight = Math.round(overlayImage.offsetHeight * scaleY);

    const formData = new FormData();
    formData.append('image', imageInput.files[0]);
    const relativeStickerPath = new URL(overlayImage.src).pathname;
    formData.append('sticker', relativeStickerPath);
    formData.append('stickerWidth', scaledStickerWidth);
    formData.append('stickerHeight', scaledStickerHeight);
    formData.append('posX', adjustedPosX);
    formData.append('posY', adjustedPosY);

    try {
      const res = await fetch(`${window.location.origin}/backend/app/process_img.php`, {
        method: 'POST',
        credentials: 'include',
        body: formData
      });

      const data = await res.json();

      if (data.success) {
        mainImage.style.display = 'none';
        overlayImage.style.display = 'none';
        responseMessage.innerHTML = '';
        responseMessage.innerHTML = `<img src="${data.image_url}" alt="Final Image" class="uploaded-img" />`;
        const container = document.querySelector('.thumbnail-container');
        const noImagesMsg = container.querySelector('.no-images-msg');
        if (noImagesMsg) noImagesMsg.remove();

        const newThumbnail = document.createElement('img');
        newThumbnail.src = data.image_url;
        newThumbnail.alt = 'Previous Image';
        newThumbnail.classList.add('thumbnail-img');
        container.prepend(newThumbnail);
      } else {
        responseMessage.textContent = data.message;
      }
    } catch (err) {
      responseMessage.textContent = 'Error processing image.';
    }
  });
});

document.addEventListener('DOMContentLoaded', async function () {
    const container = document.querySelector('.thumbnail-container');

    try {
        const res = await fetch(`${window.location.origin}/backend/app/get_user_imgs.php`, {
            method: 'GET',
            credentials: 'include',
        });

        const data = await res.json();

        if (data.success && Array.isArray(data.images)) {
          if (data.images.length === 0) {
              const msg = document.createElement('p');
              msg.textContent = 'No images yet.';
              msg.classList.add('no-images-msg');
              container.appendChild(msg);
              return;
          }

          data.images.forEach(url => {
              const img = document.createElement('img');
              img.src = url;
              img.alt = 'Previous Image';
              img.classList.add('thumbnail-img');
              container.appendChild(img);
          });
        }
    } catch (err) {
        console.error('Error fetching images:', err);
    }
});

const webcamBtn = document.querySelector('.upload-webcam');
const webcamPreview = document.getElementById('webcamPreview');
const webcamCanvas = document.getElementById('webcamCanvas');
const webcamContainer = document.getElementById('webcamContainer');
const takePhotoBtn = document.getElementById('takePhotoBtn');

let mediaStream = null;

webcamBtn.addEventListener('click', async (e) => {
  e.preventDefault();

  try {
    mediaStream = await navigator.mediaDevices.getUserMedia({ video: true });
    webcamPreview.srcObject = mediaStream;
    webcamContainer.style.display = 'flex';
    takePhotoBtn.style.display = 'flex';
    mainImage.style.display = 'none';
  } catch (err) {
    alert('Could not access webcam');
    console.error(err);
  }
});

takePhotoBtn.addEventListener('click', () => {
  if (!mediaStream) return;

  const context = webcamCanvas.getContext('2d');

  const aspectRatio = webcamPreview.videoWidth / webcamPreview.videoHeight;
  const containerWidth = webcamPreview.getBoundingClientRect().width;
  const containerHeight = containerWidth / aspectRatio;

    webcamCanvas.width = webcamPreview.videoWidth;
    webcamCanvas.height = webcamPreview.videoHeight;
  context.drawImage(webcamPreview, 0, 0, webcamCanvas.width, webcamCanvas.height);

  mediaStream.getTracks().forEach(track => track.stop());
  mediaStream = null;

  webcamContainer.style.display = 'none';

  webcamCanvas.toBlob(blob => {
    const file = new File([blob], 'webcam.jpg', { type: 'image/jpeg' });

    const imageUrl = URL.createObjectURL(file);
    mainImage.src = imageUrl;
    mainImage.style.display = 'block';

    const dataTransfer = new DataTransfer();
    dataTransfer.items.add(file);
    imageInput.files = dataTransfer.files;

    mainImage.onload = () => {
        mainImage.style.display = 'block';

        imageInput.disabled = false;
        document.querySelector('.upload-label').classList.remove('disabled');
        document.querySelector('.upload-webcam').classList.remove('disabled');
    };
  }, 'image/jpeg', 1.0);
});
