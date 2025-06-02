Array.prototype.last = function () {
    return this[this.length - 1];
};

Math.sinus = function (degree) {
    return Math.sin((degree / 180) * Math.PI);
};

let gameStartTime;
let playedSeconds = 0;
let phase = "kutilmoqda";
let lastTimestamp;
let heroX;
let heroY;
let sceneOffset;
let platforms = [];
let sticks = [];
let trees = [];
let score = 0;

const canvasWidth = 375;
const canvasHeight = 375;
const platformHeight = 100;
const heroDistanceFromEdge = 10;
const paddingX = 100;
const perfectAreaSize = 10;
const backgroundSpeedMultiplier = 0.2;
const hill1BaseHeight = 100;
const hill1Amplitude = 10;
const hill1Stretch = 1;
const hill2BaseHeight = 70;
const hill2Amplitude = 20;
const hill2Stretch = 0.5;
const stretchingSpeed = 4;
const turningSpeed = 4;
const walkingSpeed = 4;
const transitioningSpeed = 2;
const fallingSpeed = 2;
const heroWidth = 17;
const heroHeight = 30;

const canvas = document.getElementById("game");
canvas.width = window.innerWidth;
canvas.height = window.innerHeight;
const ctx = canvas.getContext("2d");
const introductionElement = document.getElementById("introduction");
const perfectElement = document.getElementById("perfect");
const restartButton = document.getElementById("restart");
const scoreElement = document.getElementById("score");
const timeElement = document.getElementById("time");
const leaderboardButton = document.getElementById("leaderboard-button");
const homeA = document.getElementById("home-a");
const scoreModal = document.getElementById("score-modal");
const closeModal = document.getElementById("close-modal");
const scoreTableBody = document.getElementById("score-table-body");

function resetGame() {
    phase = "kutilmoqda";
    lastTimestamp = undefined;
    sceneOffset = 0;
    score = 0;
    playedSeconds = 0;
    gameStartTime = undefined;
    introductionElement.style.opacity = 1;
    perfectElement.style.opacity = 0;
    restartButton.style.display = "none";
    scoreElement.innerText = `Ball: ${score}`;
    timeElement.innerText = `Vaqt: 0s`;
    platforms = [{ x: 50, w: 50 }];
    generatePlatform();
    generatePlatform();
    generatePlatform();
    generatePlatform();
    sticks = [{ x: platforms[0].x + platforms[0].w, length: 0, rotation: 0 }];
    trees = [];
    generateTree();
    generateTree();
    generateTree();
    generateTree();
    generateTree();
    generateTree();
    generateTree();
    generateTree();
    generateTree();
    generateTree();
    heroX = platforms[0].x + platforms[0].w - heroDistanceFromEdge;
    heroY = 0;
    draw();
}

function generateTree() {
    const minimumGap = 30;
    const maximumGap = 150;
    const lastTree = trees.last();
    let furthestX = lastTree ? lastTree.x : 0;
    const x = furthestX + minimumGap + Math.floor(Math.random() * (maximumGap - minimumGap));
    const treeColors = ["#6D8821", "#8FAC34", "#98B333"];
    const color = treeColors[Math.floor(Math.random() * 3)];
    trees.push({ x, color });
}

function generatePlatform() {
    const minimumGap = 40;
    const maximumGap = 200;
    const minimumWidth = 20;
    const maximumWidth = 100;
    const lastPlatform = platforms.last();
    let furthestX = lastPlatform.x + lastPlatform.w;
    const x = furthestX + minimumGap + Math.floor(Math.random() * (maximumGap - minimumGap));
    const w = minimumWidth + Math.floor(Math.random() * (maximumWidth - minimumWidth));
    platforms.push({ x, w });
}

function saveScore() {
    if (!gameStartTime) return;
    const gameEndTime = Date.now();
    playedSeconds = Math.floor((gameEndTime - gameStartTime) / 1000);
    timeElement.innerText = `Vaqt: ${playedSeconds}s`;
    fetch('./score_update.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `score=${score}&played_seconds=${playedSeconds}&game_id=${game_id}`
    })
        .then(res => res.json())
        .catch(err => console.error(err));
}

function fetchScores() {
    fetch('./get_scores.php?game_id=' + game_id)
        .then(response => response.json())
        .then(scores => {
            scores.sort((a, b) => {
                if (b.score !== a.score) {
                    return b.score - a.score;
                }
                return a.played_seconds - b.played_seconds;
            });

            scoreTableBody.innerHTML = '';

            let currentRank = 0;
            let lastScore = null;
            let lastTime = null;
            let sameRankCount = 0;

            scores.forEach(function (score, index) {
                if (score.score === lastScore && score.played_seconds === lastTime) {
                    sameRankCount++;
                } else {
                    currentRank = index + 1;
                    sameRankCount = 1;
                    lastScore = score.score;
                    lastTime = score.played_seconds;
                }

                const row = document.createElement('tr');
                if (score.username === myUsername) {
                    row.style.backgroundColor = 'rgba(0, 255, 0, 0.3)';
                    row.style.fontWeight = 'bold';
                }

                row.innerHTML = `
                    <td>${currentRank}</td>
                    <td>${score.name}</td>
                    <td>${score.username}</td>
                    <td>${score.score}</td>
                    <td>${score.played_seconds}</td>
                `;
                scoreTableBody.appendChild(row);
            });
        })
        .catch(function (err) {
            console.error('Ballarni olishda xato:', err);
            scoreTableBody.innerHTML = '<tr><td colspan="5">Ballarni yuklashda xato yuz berdi</td></tr>';
        });
}

leaderboardButton.addEventListener('click', (e) => {
    e.stopPropagation();
    scoreModal.style.display = 'flex';
    fetchScores();
});

closeModal.addEventListener('click', () => {
    scoreModal.style.display = 'none';
});

scoreModal.addEventListener('click', (e) => {
    if (e.target === scoreModal) {
        scoreModal.style.display = 'none';
    }
});

window.addEventListener("keydown", function (event) {
    if (event.key === " ") {
        event.preventDefault();
        resetGame();
    }
});

window.addEventListener("mousedown", function (event) {
    const excludedElements = [homeA, timeElement, scoreElement, leaderboardButton];

    if (
        phase === "kutilmoqda" &&
        !excludedElements.includes(event.target) &&
        !scoreModal.contains(event.target)
    ) {
        if (!gameStartTime) {
            gameStartTime = Date.now();
        }
        lastTimestamp = undefined;
        introductionElement.style.opacity = 0;
        phase = "cho‘zish";
        window.requestAnimationFrame(animate);
    }
});

window.addEventListener("mouseup", function (event) {
    if (phase === "cho‘zish") {
        phase = "burilish";
    }
});

window.addEventListener("resize", function () {
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    draw();
});

window.addEventListener("touchstart", function (event) {
    if (phase === "kutilmoqda" && event.target !== leaderboardButton && !scoreModal.contains(event.target)) {
        if (!gameStartTime) {
            gameStartTime = Date.now();
        }
        lastTimestamp = undefined;
        introductionElement.style.opacity = 0;
        phase = "cho‘zish";
        window.requestAnimationFrame(animate);
    }
});

window.addEventListener("touchend", function (event) {
    if (phase === "cho‘zish") {
        phase = "burilish";
    }
});

restartButton.addEventListener("click", function (event) {
    event.preventDefault();
    resetGame();
    restartButton.style.display = "none";
});

function animate(timestamp) {
    if (!lastTimestamp) {
        lastTimestamp = timestamp;
        window.requestAnimationFrame(animate);
        return;
    }

    if (gameStartTime && phase !== "kutilmoqda") {
        playedSeconds = Math.floor((Date.now() - gameStartTime) / 1000);
        timeElement.innerText = `Vaqt: ${playedSeconds}s`;
    }

    switch (phase) {
        case "kutilmoqda":
            return;
        case "cho‘zish":
            sticks.last().length += (timestamp - lastTimestamp) / stretchingSpeed;
            break;
        case "burilish":
            sticks.last().rotation += (timestamp - lastTimestamp) / turningSpeed;
            if (sticks.last().rotation > 90) {
                sticks.last().rotation = 90;
                const [nextPlatform, perfectHit] = thePlatformTheStickHits();
                if (nextPlatform) {
                    score += perfectHit ? 2 : 1;
                    scoreElement.innerText = `Ball: ${score}`;
                    if (perfectHit) {
                        perfectElement.style.opacity = 1;
                        setTimeout(() => (perfectElement.style.opacity = 0), 1000);
                    }
                    generatePlatform();
                    generateTree();
                    generateTree();
                }
                phase = "yurish";
            }
            break;
        case "yurish":
            heroX += (timestamp - lastTimestamp) / walkingSpeed;
            const [nextPlatform] = thePlatformTheStickHits();
            if (nextPlatform) {
                const maxHeroX = nextPlatform.x + nextPlatform.w - heroDistanceFromEdge;
                if (heroX > maxHeroX) {
                    heroX = maxHeroX;
                    phase = "o‘tish";
                }
            } else {
                const maxHeroX = sticks.last().x + sticks.last().length + heroWidth;
                if (heroX > maxHeroX) {
                    heroX = maxHeroX;
                    phase = "yiqilish";
                }
            }
            break;
        case "o‘tish":
            sceneOffset += (timestamp - lastTimestamp) / transitioningSpeed;
            const [nextPlatformTransition] = thePlatformTheStickHits();
            if (sceneOffset > nextPlatformTransition.x + nextPlatformTransition.w - paddingX) {
                sticks.push({
                    x: nextPlatformTransition.x + nextPlatformTransition.w,
                    length: 0,
                    rotation: 0
                });
                phase = "kutilmoqda";
            }
            break;
        case "yiqilish":
            if (sticks.last().rotation < 180) {
                sticks.last().rotation += (timestamp - lastTimestamp) / turningSpeed;
            }
            heroY += (timestamp - lastTimestamp) / fallingSpeed;
            const maxHeroY = platformHeight + 100 + (window.innerHeight - canvasHeight) / 2;
            if (heroY > maxHeroY) {
                saveScore();
                restartButton.style.display = "block";
                return;
            }
            break;
        default:
            throw new Error("Noto‘g‘ri o‘yin bosqichi");
    }

    draw();
    lastTimestamp = timestamp;
    window.requestAnimationFrame(animate);
}

function thePlatformTheStickHits() {
    if (sticks.last().rotation !== 90) {
        throw new Error(`Tayoqning burilishi ${sticks.last().rotation}°`);
    }
    const stickFarX = sticks.last().x + sticks.last().length;
    const platformTheStickHits = platforms.find(
        (platform) => platform.x < stickFarX && stickFarX < platform.x + platform.w
    );
    if (
        platformTheStickHits &&
        platformTheStickHits.x + platformTheStickHits.w / 2 - perfectAreaSize / 2 < stickFarX &&
        stickFarX < platformTheStickHits.x + platformTheStickHits.w / 2 + perfectAreaSize / 2
    ) {
        return [platformTheStickHits, true];
    }
    return [platformTheStickHits, false];
}

function draw() {
    ctx.save();
    ctx.clearRect(0, 0, window.innerWidth, window.innerHeight);
    drawBackground();
    ctx.translate(
        (window.innerWidth - canvasWidth) / 2 - sceneOffset,
        (window.innerHeight - canvasHeight) / 2
    );
    drawPlatforms();
    drawHero();
    drawSticks();
    ctx.restore();
}

function drawPlatforms() {
    platforms.forEach(({ x, w }) => {
        ctx.fillStyle = "black";
        ctx.fillRect(
            x,
            canvasHeight - platformHeight,
            w,
            platformHeight + (window.innerHeight - canvasHeight) / 2
        );
        if (sticks.last().x < x) {
            ctx.fillStyle = "red";
            ctx.fillRect(
                x + w / 2 - perfectAreaSize / 2,
                canvasHeight - platformHeight,
                perfectAreaSize,
                perfectAreaSize
            );
        }
    });
}

function drawHero() {
    ctx.save();
    ctx.fillStyle = "black";
    ctx.translate(
        heroX - heroWidth / 2,
        heroY + canvasHeight - platformHeight - heroHeight / 2
    );
    drawRoundedRect(-heroWidth / 2, -heroHeight / 2, heroWidth, heroHeight - 4, 5);
    const legDistance = 5;
    ctx.beginPath();
    ctx.arc(legDistance, 11.5, 3, 0, Math.PI * 2, false);
    ctx.fill();
    ctx.beginPath();
    ctx.arc(-legDistance, 11.5, 3, 0, Math.PI * 2, false);
    ctx.fill();
    ctx.beginPath();
    ctx.fillStyle = "white";
    ctx.arc(5, -7, 3, 0, Math.PI * 2, false);
    ctx.fill();
    ctx.fillStyle = "red";
    ctx.fillRect(-heroWidth / 2 - 1, -12, heroWidth + 2, 4.5);
    ctx.beginPath();
    ctx.moveTo(-9, -14.5);
    ctx.lineTo(-17, -18.5);
    ctx.lineTo(-14, -8.5);
    ctx.fill();
    ctx.beginPath();
    ctx.moveTo(-10, -10.5);
    ctx.lineTo(-15, -3.5);
    ctx.lineTo(-5, -7);
    ctx.fill();
    ctx.restore();
}

function drawRoundedRect(x, y, width, height, radius) {
    ctx.beginPath();
    ctx.moveTo(x, y + radius);
    ctx.lineTo(x, y + height - radius);
    ctx.arcTo(x, y + height, x + radius, y + height, radius);
    ctx.lineTo(x + width - radius, y + height);
    ctx.arcTo(x + width, y + height, x + width, y + height - radius, radius);
    ctx.lineTo(x + width, y + radius);
    ctx.arcTo(x + width, y, x + width - radius, y, radius);
    ctx.lineTo(x + radius, y);
    ctx.arcTo(x, y, x, y + radius, radius);
    ctx.fill();
}

function drawSticks() {
    sticks.forEach((stick) => {
        ctx.save();
        ctx.translate(stick.x, canvasHeight - platformHeight);
        ctx.rotate((Math.PI / 180) * stick.rotation);
        ctx.beginPath();
        ctx.lineWidth = 2;
        ctx.moveTo(0, 0);
        ctx.lineTo(0, -stick.length);
        ctx.stroke();
        ctx.restore();
    });
}

function drawBackground() {
    const gradient = ctx.createLinearGradient(0, 0, 0, window.innerHeight);
    gradient.addColorStop(0, "#BBD691");
    gradient.addColorStop(1, "#FEF1E1");
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, window.innerWidth, window.innerHeight);
    drawHill(hill1BaseHeight, hill1Amplitude, hill1Stretch, "#95C629");
    drawHill(hill2BaseHeight, hill2Amplitude, hill2Stretch, "#659F1C");
    trees.forEach((tree) => drawTree(tree.x, tree.color));
}

function drawHill(baseHeight, amplitude, stretch, color) {
    ctx.beginPath();
    ctx.moveTo(0, window.innerHeight);
    ctx.lineTo(0, getHillY(0, baseHeight, amplitude, stretch));
    for (let i = 0; i < window.innerWidth; i++) {
        ctx.lineTo(i, getHillY(i, baseHeight, amplitude, stretch));
    }
    ctx.lineTo(window.innerWidth, window.innerHeight);
    ctx.fillStyle = color;
    ctx.fill();
}

function drawTree(x, color) {
    ctx.save();
    ctx.translate(
        (-sceneOffset * backgroundSpeedMultiplier + x) * hill1Stretch,
        getTreeY(x, hill1BaseHeight, hill1Amplitude)
    );
    const treeTrunkHeight = 5;
    const treeTrunkWidth = 2;
    const treeCrownHeight = 25;
    const treeCrownWidth = 10;
    ctx.fillStyle = "#7D833C";
    ctx.fillRect(
        -treeTrunkWidth / 2,
        -treeTrunkHeight,
        treeTrunkWidth,
        treeTrunkHeight
    );
    ctx.beginPath();
    ctx.moveTo(-treeCrownWidth / 2, -treeTrunkHeight);
    ctx.lineTo(0, -(treeTrunkHeight + treeCrownHeight));
    ctx.lineTo(treeCrownWidth / 2, -treeTrunkHeight);
    ctx.fillStyle = color;
    ctx.fill();
    ctx.restore();
}

function getHillY(windowX, baseHeight, amplitude, stretch) {
    const sineBaseY = window.innerHeight - baseHeight;
    return Math.sinus((sceneOffset * backgroundSpeedMultiplier + windowX) * stretch) * amplitude + sineBaseY;
}

function getTreeY(x, baseHeight, amplitude) {
    const sineBaseY = window.innerHeight - baseHeight;
    return Math.sinus(x) * amplitude + sineBaseY;
}

function goHome() {
    if (confirm("Bosh sahifaga o‘tishni xohlaysizmi?")) {
        const path = location.pathname.replace(/\/[^\/]*$/, '/');
        location.href = path;
    }
}

resetGame();
window.requestAnimationFrame(animate);