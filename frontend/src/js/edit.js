const imageInput = document.getElementById('imageInput');
const mainImage = document.getElementById('mainImage');
const overlayImage = document.getElementById('overlayImage');

document.querySelectorAll('.side-container img').forEach(img => {
    img.addEventListener('click', () => {
        if (mainImage.src) {
            overlayImage.src = img.src;
            overlayImage.style.display = 'block';
            imageInput.disabled = false;
        } else {
            alert("Primero selecciona o sube una imagen.");
        }
    });
});

let isDragging = false;
let offsetX, offsetY;

// Elementos
const overlay = document.getElementById('overlayImage');
const editorArea = document.getElementById('imageEditorArea');

// Función para obtener las coordenadas de un evento (ya sea mouse o touch)
function getEventCoords(e) {
    const rect = editorArea.getBoundingClientRect();
    if (e.touches) {
        // Si es un evento táctil, tomamos el primer toque
        return {
            x: e.touches[0].clientX - rect.left,
            y: e.touches[0].clientY - rect.top
        };
    } else {
        // Si es un evento de mouse, usamos clientX y clientY
        return {
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
        };
    }
}

// Iniciar el drag (para ratón y para tacto)
function startDrag(e) {
    isDragging = true;
    const coords = getEventCoords(e);
    const overlayRect = overlay.getBoundingClientRect(); // Coordenadas del overlay
    offsetX = coords.x - (overlayRect.left - editorArea.getBoundingClientRect().left);
    offsetY = coords.y - (overlayRect.top - editorArea.getBoundingClientRect().top);
}

// Detener el drag
function stopDrag() {
    isDragging = false;
}

// Mover el sticker mientras se arrastra
function moveDrag(e) {
    if (isDragging) {
        const coords = getEventCoords(e);
        let x = coords.x - offsetX;
        let y = coords.y - offsetY;

        const rect = editorArea.getBoundingClientRect();
        
        // Limitar dentro del contenedor
        x = Math.max(0, Math.min(x, rect.width - overlay.offsetWidth));
        y = Math.max(0, Math.min(y, rect.height - overlay.offsetHeight));

        overlay.style.left = `${x}px`;
        overlay.style.top = `${y}px`;
    }
}

// Eventos de ratón
overlay.addEventListener('mousedown', startDrag);
document.addEventListener('mousemove', moveDrag);
document.addEventListener('mouseup', stopDrag);

// Eventos táctiles
overlay.addEventListener('touchstart', startDrag);
document.addEventListener('touchmove', moveDrag);
document.addEventListener('touchend', stopDrag);

document.addEventListener('DOMContentLoaded', function () {
  const postButton = document.querySelector('.upload-btn');
  /* const imageInput = document.getElementById('imageInput'); */
  /* const overlayImage = document.getElementById('overlayImage'); */
  const responseMessage = document.getElementById('responseMessage');

  postButton.addEventListener('click', async function (e) {
    e.preventDefault();

    if (!imageInput.files.length || !overlayImage.src) {
      alert('Debes seleccionar una imagen y un sticker.');
      return;
    }
    const realImage = document.querySelector('.uploaded-img');
    if (!realImage || !realImage.complete || realImage.naturalWidth === 0) {
        alert("La imagen aún no se ha cargado completamente.");
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
    formData.append('sticker', relativeStickerPath); // Ejemplo: /images/monkey.png
    formData.append('stickerWidth', scaledStickerWidth);
    formData.append('stickerHeight', scaledStickerHeight);
    formData.append('posX', adjustedPosX);
    formData.append('posY', adjustedPosY);

    console.log('Imagen que se envía:', imageInput.files[0]);
    console.log({
    scaleX,
    scaleY,
    adjustedPosX,
    adjustedPosY,
    overlayWidth: overlayImage.offsetWidth,
    overlayHeight: overlayImage.offsetHeight,
    scaledStickerWidth,
    scaledStickerHeight,
    stickerSrc: overlayImage.src
    });
    try {
      const res = await fetch(`${window.location.origin}/backend/app/process_img.php`, {
        method: 'POST',
        credentials: 'include',
        body: formData
      });

      const data = await res.json();

      if (data.success) {
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
        } else {
            console.log('Error loading images:', data.message ||  'No images yet.');
        }
    } catch (err) {
        /* console.error('Error fetching images:', err); */
    }
});
