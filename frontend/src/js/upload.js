document.addEventListener('DOMContentLoaded', function () {
  const form = document.getElementById('uploadForm');
  const imageInput = document.getElementById('imageInput');
  const responseMessage = document.getElementById('responseMessage');

  imageInput.addEventListener('change', function () {
    if (imageInput.files.length > 0) {
      form.dispatchEvent(new Event('submit'));
    }
  });

  form.addEventListener('submit', async function (e) {
    e.preventDefault();

    if (!imageInput.files.length) {
      responseMessage.textContent = 'Please select an image.';
      return;
    }

    const formData = new FormData();
    formData.append('image', imageInput.files[0]);

    try {
      const res = await fetch(`${window.location.origin}/backend/app/upload.php`, {
        method: 'POST',
        credentials: 'include',
        body: formData,
      });

      if (res.status === 413) {
        responseMessage.style.color = 'red';
        responseMessage.textContent = 'The uploaded file is too large. Please choose a smaller image.';
        return;
      }

      const data = await res.json();

      if (data.success) {
        responseMessage.style.color = 'green';
        responseMessage.textContent = "Loaded image";

        const img = document.createElement('img');
        img.src = data.image_url;
        img.alt = 'Uploaded image';
        img.classList.add('uploaded-img');

        responseMessage.innerHTML = '';
        responseMessage.appendChild(img);
      } else {
        responseMessage.style.color = 'red';
        responseMessage.textContent = data.message;
      }
    } catch (error) {
      responseMessage.style.color = 'red';
      responseMessage.textContent = 'An unexpected error occurred.';
    }
  });
});
