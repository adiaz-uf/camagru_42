document.addEventListener('DOMContentLoaded', async function () {
    const galeryContainer = document.querySelector('.galery');

    try {
        let res;
        if (typeof profile !== 'undefined') {
            res = await fetch(`${window.location.origin}/backend/app/get_user_posted_imgs.php`, {
                method: 'GET',
                credentials: 'include',
            });
        } else {
            res = await fetch(`${window.location.origin}/backend/app/get_posted_imgs.php`, {
                method: 'GET',
                credentials: 'include',
            });
        }

        const data = await res.json();

        if (data.success && Array.isArray(data.images)) {
            if (data.images.length === 0) {
                galeryContainer.innerHTML = '<h3>No posted images yet</h3>';
                galeryContainer.classList.add('single');
                return;
            }

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

                // Delete Image (if profile mode)
                if (typeof profile !== 'undefined') {
                    const deleteBtn = card.querySelector('.delete-btn');
                    deleteBtn.addEventListener('click', async () => {
                        const imageId = deleteBtn.dataset.id;
                        const confirmed = confirm('Are you sure you want to delete this image?');
                        if (!confirmed) return;

                        try {
                            const deleteRes = await fetch(`${window.location.origin}/backend/app/delete_img.php`, {
                                method: 'POST',
                                headers: { 'Content-Type': 'application/json' },
                                credentials: 'include',
                                body: JSON.stringify({ image_id: imageId }),
                            });

                            const result = await deleteRes.json();
                            if (result.success) {
                                card.remove();
                                if (galeryContainer.children.length === 1) {
                                    galeryContainer.classList.add('single');
                                } else {
                                    galeryContainer.classList.remove('single');
                                }
                            } else {
                                alert('Failed to delete image.');
                            }
                        } catch (err) {
                            console.error('Delete failed:', err);
                        }
                    });
                }

                if (galeryContainer.children.length === 1) {
                    galeryContainer.classList.add('single');
                } else {
                    galeryContainer.classList.remove('single');
                }
            });
        } else {
            console.log('Error loading images:', data.message || 'No images yet.');
        }
    } catch (err) {
        console.error('Error fetching images:', err);
    }

    // 🔧 Renders the interactive footer of a card
    function renderFooter(card, url) {
        const footer = card.querySelector('.card-footer');

        // Display current like and comment counts
        const likeCount = url.likes_count || 0;
        const commentCount = url.comments_count || 0;

        footer.innerHTML = `
        <button class="comments-btn">💬 ${commentCount}</button>
        <span class="like-btn">❤️ ${likeCount}</span>
        `;

        // ❤️ Like button logic
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
                    url.likes_count = Math.max(newCount, 0); // update local state
                } else {
                    alert('Please, Sign In first');
                }
            } catch (err) {
                console.error('Error liking image:', err);
            }
        });

        // 💬 View Comments logic
        footer.querySelector('.comments-btn').addEventListener('click', async () => {
            try {
                const res = await fetch(`${window.location.origin}/backend/app/get_img_comments.php?image_id=${url.id}`);
                const data = await res.json();

                if (data.success) {
                    const commentsWrapper = document.createElement('div');
                    commentsWrapper.className = 'comments-wrapper';

                    const commentsHtml = data.comments.length
                        ? data.comments.map(c => `<p style="color: #000;"><strong>${c.username}</strong>: ${c.content}</p>`).join('')
                        : '<p style="color: #000;">No comments yet.</p>';

                    commentsWrapper.innerHTML = `
                        <div class="comments-list" style="text-align:left; max-height: 100px; overflow-y: auto; width:100%; scrollbar-width:none;">
                            ${commentsHtml}
                        </div>
                        <input class="comment-input" type="text" placeholder="Write a comment..." style="width: 100%; margin-top: 0.5rem; padding: 0.5rem; border-radius: 6px; border: none;"/>
                        <div style="display: flex; justify-content: center; gap: 1rem; width: 100%;">
                            <button class="btn close-comments-btn" style="margin-top: 1rem;">Close</button>
                            <button class="btn add-comments-btn" style="margin-top: 1rem;">Add comment</button>
                        </div>
                    `;

                    footer.innerHTML = '';
                    footer.appendChild(commentsWrapper);

                    commentsWrapper.querySelector('.close-comments-btn').addEventListener('click', () => {
                        renderFooter(card, url); // restore footer with updated count
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
                            renderFooter(card, url); // refresh UI
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
});
