document.addEventListener('DOMContentLoaded', async function () {
    const galeryContainer = document.querySelector('.galery');

     try {
        const res = await fetch(`${window.location.origin}/backend/app/get_posted_imgs.php`, {
            method: 'GET',
            credentials: 'include',
        });

        const data = await res.json();

        if (data.success && Array.isArray(data.images)) {
          if (data.images.length === 0) {
            galeryContainer.innerHTML = '<p>No hay imágenes para mostrar.</p>';
            return;
          }

          data.images.forEach(url => {
             const card = document.createElement('div');
                card.className = 'card';

                card.innerHTML = `
                    <img src="${url.image_url}" alt="${url.caption || ''}">
                    <p class="caption"><strong> ${url.caption ? url.username + ':' || '' : ''}</strong> ${url.caption || ''}</p>
                    <div class="card-footer">
                        <span>❤️ 0</span>
                        <button class="comments-btn">💬 13</button>
                        <button class="btn">Add Comment</button>
                    </div>
                `;

                galeryContainer.appendChild(card);
          });
        } else {
            console.log('Error loading images:', data.message ||  'No images yet.');
        }
    } catch (err) {
        console.error('Error fetching images:', err);
    }
});
