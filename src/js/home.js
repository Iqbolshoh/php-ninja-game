const createBtn = document.getElementById('createBtn');
createBtn.addEventListener('click', () => {
    Swal.fire({
        icon: 'question',
        title: 'Yangi oʻyin yaratish?',
        text: 'Haqiqatan ham yangi oʻyin havolasini yaratmoqchimisiz?',
        background: '#000000',
        color: '#e0e0e0',
        showCancelButton: true,
        confirmButtonText: 'Ha, yarat!',
        cancelButtonText: 'Bekor qilish',
        confirmButtonColor: '#00cc66',
        cancelButtonColor: '#555555',
    }).then((result) => {
        if (result.isConfirmed) {
            generateGameLink();
        }
    });
});

function generateGameLink() {
    const createBtn = document.getElementById('createBtn');
    const gameId = Math.random().toString(36).substring(2, 18);
    const timestamp = Date.now();
    const gameLink = `${window.location.href.replace(/\/$/, '')}/game/?link=${gameId}-${timestamp.toString().slice(-10)}`;

    const formData = new URLSearchParams();
    formData.append('link', gameLink);

    fetch('game/create.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: formData
    })
        .then(res => {
            if (!res.ok) throw new Error('HTTP xatosi ' + res.status);
            return res.json();
        })
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: 'Muvaffaqiyat!',
                    text: data.message,
                    background: '#000000',
                    color: '#e0e0e0',
                    confirmButtonColor: '#00cc66',
                    confirmButtonText: 'OK',
                    timer: 2000,
                    timerProgressBar: true
                });
                fetchGames();
                createParticles(createBtn);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Muvaffaqiyatsiz!',
                    text: data.message,
                    background: '#000000',
                    color: '#e0e0e0',
                    confirmButtonColor: '#ff3333',
                    confirmButtonText: 'OK'
                });
            }
        })
        .catch(err => {
            console.error('Fetch xatosi:', err);
            Swal.fire({
                icon: 'error',
                title: 'Tarmoq xatosi!',
                text: 'Nimadir noto‘g‘ri ketdi. Iltimos, ulanishingizni tekshiring va qayta urinib ko‘ring.',
                background: '#000000',
                color: '#e0e0e0',
                confirmButtonColor: '#ff3333',
                confirmButtonText: 'OK'
            });
        });
}

function createParticles(targetElement) {
    if (!targetElement) return;

    const rect = targetElement.getBoundingClientRect();
    const x = rect.left + rect.width / 2;
    const y = rect.top + rect.height / 2;

    for (let i = 0; i < 12; i++) {
        const particle = document.createElement('div');
        particle.classList.add('particle');
        document.body.appendChild(particle);

        const angle = Math.random() * 360;
        const distance = Math.random() * 60 + 20;
        const xPos = x + Math.cos(angle * Math.PI / 180) * distance;
        const yPos = y + Math.sin(angle * Math.PI / 180) * distance;

        particle.style.left = `${xPos}px`;
        particle.style.top = `${yPos}px`;
        particle.style.position = 'fixed';
        particle.style.width = '6px';
        particle.style.height = '6px';
        particle.style.background = 'red';
        particle.style.borderRadius = '50%';
        particle.style.pointerEvents = 'none';
        particle.style.zIndex = 9999;
        particle.style.transition = 'transform 0.5s ease-out, opacity 0.5s ease-out';

        requestAnimationFrame(() => {
            particle.style.transform = `translate(${Math.random() * 100 - 50}px, ${Math.random() * 100 - 50}px) scale(0)`;
            particle.style.opacity = '0';
        });

        setTimeout(() => particle.remove(), 600);
    }
}

function showError(message) {
    Swal.fire({
        icon: 'error',
        title: 'Xato!',
        text: message,
        background: '#000000',
        color: '#e0e0e0',
        confirmButtonColor: '#ff0000'
    });
}

function fetchGames() {
    fetch('game/get_games.php')
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderGames(data.data);
            } else {
                showError('O‘yinlarni olish muvaffaqiyatsiz yakunlandi.');
            }
        })
        .catch(error => {
            console.error('Fetch xatosi:', error);
            showError('Tarmoq xatosi. Iltimos, keyinroq qayta urinib ko‘ring.');
        });
}

function renderGames(games) {
    const myGames = document.getElementById('my-games');
    myGames.innerHTML = '<h3>Mening oʻyinlarim</h3><br>';

    games.forEach((game, index) => {
        const gameItem = document.createElement('div');
        gameItem.classList.add('game-item');

        const date = new Date(game.created_at);
        const formattedTime = date.toLocaleString('uz-UZ', {
            month: 'short',
            day: 'numeric',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });

        gameItem.innerHTML = `
                <div class="game-info">
                    <a href="${game.link}" class="game-link" target="_blank">${game.link}</a>
                    <div class="game-meta">
                        <span class="game-time"><i class="fas fa-clock icon"></i> ${formattedTime}</span>
                        <span class="game-players"><i class="fas fa-users icon"></i> ${game.players_count} o‘yinchilar</span>
                    </div>
                </div>
                <div class="game-actions">
                    <button class="btn btn-secondary btn-small" onclick="copyGameLink('${game.link}', this)">
                        <i class="fas fa-copy icon"></i> Nusxalash
                    </button>
                    <button class="btn btn-small" onclick="viewGame('${game.link}', this)">
                        <i class="fas fa-play icon"></i> O‘ynash
                    </button>
                    <button class="btn btn-danger btn-small" onclick="deleteGame(${game.id}, this)">
                        <i class="fas fa-trash icon"></i> O‘chirish
                    </button>
                </div>
            `;

        myGames.appendChild(gameItem);
        setTimeout(() => gameItem.classList.add('show'), index * 200);
    });
}

function copyGameLink(link, btn) {
    if (!navigator.clipboard) {
        const textarea = document.createElement('textarea');
        textarea.value = link;
        document.body.appendChild(textarea);
        textarea.select();
        try {
            document.execCommand('copy');
            btn.innerHTML = '<i class="fas fa-check icon"></i> Nusxalandi!';
            btn.style.background = 'green';
        } catch (err) {
            showError('Nusxalash muvaffaqiyatsiz yakunlandi.');
        }
        document.body.removeChild(textarea);
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-copy icon"></i> Nusxalash';
            btn.style.background = '';
        }, 2000);
        createParticles(btn);
        return;
    }

    navigator.clipboard.writeText(link).then(() => {
        btn.innerHTML = '<i class="fas fa-check icon"></i> Nusxalandi!';
        btn.style.background = 'green';
        setTimeout(() => {
            btn.innerHTML = '<i class="fas fa-copy icon"></i> Nusxalash';
            btn.style.background = '';
        }, 2000);
        createParticles(btn);
    }).catch(err => {
        console.error('Clipboard xatosi:', err);
        showError('Nusxalash muvaffaqiyatsiz yakunlandi.');
    });
}

function viewGame(link, button) {
    window.open(link, '_blank');
    createParticles(button);
}

function deleteGame(id, button) {
    Swal.fire({
        icon: 'warning',
        title: 'Ishonchingiz komilmi?',
        text: 'Haqiqatan ham bu o‘yinni o‘chirmoqchimisiz?',
        background: '#000000',
        color: '#e0e0e0',
        showCancelButton: true,
        confirmButtonText: 'Ha, o‘chir!',
        cancelButtonText: 'Bekor qilish',
        confirmButtonColor: '#ff3333',
        cancelButtonColor: '#555555',
    }).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('id', id);

            fetch('game/delete.php', {
                method: 'POST',
                body: formData
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire({
                            icon: 'success',
                            title: 'O‘chirildi!',
                            text: data.message,
                            background: '#000000',
                            color: '#e0e0e0',
                            timer: 2000,
                            showConfirmButton: false
                        });
                        fetchGames();
                        createParticles(button);
                    } else {
                        showError(data.message);
                    }
                })
                .catch(error => {
                    console.error('Xato:', error);
                    showError('Tarmoq xatosi! Iltimos, qayta urinib ko‘ring.');
                });
        }
    });
}

function logout() {
    Swal.fire({
        title: 'Ishonchingiz komilmi?',
        text: "Siz tizimdan chiqasiz!",
        icon: 'warning',
        background: '#000000',
        color: '#e0e0e0',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Ha, chiqar!'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Chiqildi!',
                text: 'Siz muvaffaqiyatli tizimdan chiqdingiz.',
                icon: 'success',
                background: '#000000',
                color: '#e0e0e0',
                showConfirmButton: false,
                timer: 1500
            }).then(() => {
                window.location.href = './logout/';
            });
        }
    });
}

fetchGames();