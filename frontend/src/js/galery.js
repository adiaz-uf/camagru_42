let currentPage = 1;
let isLoading = false;
let hasMore = true;
const galeryContainer = document.querySelector('.galery');

async function loadImages() {
    if (isLoading || !hasMore) return;
    isLoading = true;

    try {
        const endpoint = typeof profile !== 'undefined'
            ? '/backend/app/get_user_posted_imgs.php'
            : '/backend/app/get_posted_imgs.php';

        const res = await fetch(`${window.location.origin}${endpoint}?page=${currentPage}&limit=5`, {
            method: 'GET',
            credentials: 'include',
        });

        const data = await res.json();

        if (data.success && Array.isArray(data.images)) {
            data.images.forEach(url => {
                const card = document.createElement('div');
                card.className = 'card';

                card.innerHTML = `
                    <img src="${url.image_url}" alt="${url.caption || ''}">
                    <p class="caption"><strong>${url.caption ? url.username + ':' : ''}</strong> ${url.caption || ''}</p>
                    <div class="card-footer"></div>
                    ${
                        typeof profile !== 'undefined'
                            ? `<button class="btn delete-btn" data-id="${url.id}">Delete Image</button>`
                            : ''
                    }
                `;

                renderFooter(card, url);
                galeryContainer.appendChild(card);

                if (typeof profile !== 'undefined') {
                    const deleteBtn = card.querySelector('.delete-btn');
                    deleteBtn.addEventListener('click', async () => {
                        if (!confirm('Delete this image?')) return;
                        const res = await fetch(`${window.location.origin}/backend/app/delete_img.php`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            credentials: 'include',
                            body: JSON.stringify({ image_id: url.id }),
                        });

                        const result = await res.json();
                        if (result.success) {
                            card.remove();
                            if (galeryContainer.children.length === 1) {
                                galeryContainer.classList.add('single');
                            } else {
                                galeryContainer.classList.remove('single');
                            }
                        } else alert('Failed to delete image');
                    });
                }

                if (galeryContainer.children.length === 1) {
                    galeryContainer.classList.add('single');
                } else {
                    galeryContainer.classList.remove('single');
                }
            });

            currentPage++;
            hasMore = data.has_more;
        } else {
            if (galeryContainer.children.length === 0) {
                galeryContainer.innerHTML = '<h1 style="color: #fff;">No posted images yet</h1>';
                galeryContainer.classList.add('single');
            }
            hasMore = false;
        }
    } catch (err) {
        console.error('Error loading images:', err);
    } finally {
        isLoading = false;
    }
}

// Renders the interactive footer of a card
function renderFooter(card, url) {
    const footer = card.querySelector('.card-footer');

    const likeCount = url.likes_count || 0;
    const commentCount = url.comments_count || 0;

    footer.innerHTML = `
        <button class="comments-btn">💬 ${commentCount}</button>
        <span class="like-btn">❤️ ${likeCount}</span>
    `;

    footer.querySelector('.like-btn').addEventListener('click', async (e) => {
        try {
            const res = await fetch(`${window.location.origin}/backend/app/like_img.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({ image_id: url.id }),
            });

            const data = await res.json();
            if (data.success) {
                const newCount = data.liked ? likeCount + 1 : likeCount - 1;
                e.target.textContent = `❤️ ${Math.max(newCount, 0)}`;
                url.likes_count = Math.max(newCount, 0);
            } else {
                alert('Please, Sign In first');
            }
        } catch (err) {
            console.error('Error liking image:', err);
        }
    });

    footer.querySelector('.comments-btn').addEventListener('click', async () => {
        try {
            const res = await fetch(`${window.location.origin}/backend/app/get_img_comments.php?image_id=${url.id}`);
            const data = await res.json();

            if (data.success) {
                const commentsWrapper = document.createElement('div');
                commentsWrapper.className = 'comments-wrapper';

                const commentsHtml = data.comments.length
                    ? data.comments.map(c => `<p style="color: #000;margin: 0; padding: 0;"><strong>${c.username}</strong>: ${c.content}</p>`).join('')
                    : '<p style="color: #000;">No comments yet.</p>';

                commentsWrapper.innerHTML = `
                    <div class="comments-list">
                        ${commentsHtml}
                    </div>
                    <input class="comment-input" type="text" placeholder="Write a comment..." style="width:95%; margin-top: 0.5rem;padding: 0.5rem; border-radius: 6px; border: none;"/>
                    <div style="display: flex; justify-content: center; gap: 1rem; width: 100%;">
                        <button class="btn close-comments-btn" style="margin-top: 1rem;">Close</button>
                        <button class="btn add-comments-btn" style="margin-top: 1rem;">Add comment</button>
                    </div>
                `;

                footer.innerHTML = '';
                footer.appendChild(commentsWrapper);

                commentsWrapper.querySelector('.close-comments-btn').addEventListener('click', () => {
                    renderFooter(card, url);
                });

                commentsWrapper.querySelector('.add-comments-btn').addEventListener('click', async () => {
                    const input = commentsWrapper.querySelector('.comment-input');
                    const content = input.value.trim();
                    if (!content) return;

                    try {
                        const res = await fetch(`${window.location.origin}/backend/app/add_comment.php`, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            credentials: 'include',
                            body: JSON.stringify({ image_id: url.id, content }),
                        });

                        const result = await res.json();
                        if (result.success) {
                            input.value = '';
                            url.comments_count += 1;
                            renderFooter(card, url);
                        } else {
                            alert(result.message || 'Failed to add comment.');
                        }
                    } catch (err) {
                        console.error('Error posting comment:', err);
                    }
                });
            } else {
                alert('Failed to load comments.');
            }
        } catch (err) {
            console.error('Fetch comments error:', err);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    loadImages();
});


const sentinel = document.getElementById('scroll-sentinel');
const observer = new IntersectionObserver(entries => {
    if (entries[0].isIntersecting) {
        loadImages();
    }
}, {
    root: null,
    rootMargin: '100px',
    threshold: 0.1
});

observer.observe(sentinel);
