{{-- EPAS-E Page Loader - Circuit Board Animation --}}
<div id="page-loader" class="page-loader {{ session('show_login_loader') ? '' : 'hidden' }}">
    <canvas id="circuit-canvas"></canvas>
</div>

<style>
.page-loader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 99999;
    background: #0c3a2d;
    animation: pulseBg 1.6s ease-in-out infinite;
    transition: opacity 0.3s ease, visibility 0.3s ease;
}

@keyframes pulseBg {
    0%, 100% { background: #0c3a2d; }
    50% { background: #6d9773; }
}

.page-loader.hidden {
    opacity: 0;
    visibility: hidden;
    pointer-events: none;
}

#circuit-canvas {
    border-radius: 8px;
}
</style>

<script>
(function() {
    var canvas = document.getElementById('circuit-canvas');
    if (!canvas) return;

    var ctx = canvas.getContext('2d', { willReadFrequently: true });
    var width = 200;
    var height = 200;

    canvas.width = width;
    canvas.height = height;
    canvas.style.width = '150px';
    canvas.style.height = '150px';

    // Yellow/green color scheme
    var traceColor = '#ffb902';
    var traceFill = '#fff';

    ctx.strokeStyle = traceColor;
    ctx.fillStyle = traceFill;
    ctx.lineWidth = 2;

    function Trace(settings) {
        settings = settings || {};
        this.x = settings.x || Math.ceil((Math.random() * width) / 4) * 4;
        this.y = settings.y || Math.ceil((Math.random() * height) / 4) * 4;

        this.points = [];
        this.points.push({
            x: this.x,
            y: this.y,
            arc: 0
        });

        this.trapCount = 0;
        this.live = true;
        this.lastPoint = this.points[0];
        this.angle = settings.angle || (Math.ceil((Math.random() * 360) / 45) * 45) * (Math.PI / 180);
        this.speed = 4;
    }

    Trace.prototype.update = function() {
        var x = this.lastPoint.x,
            y = this.lastPoint.y,
            dx = this.x - x,
            dy = this.y - y;

        if (Math.random() > 0.01) {
            var velX = Math.cos(this.angle) * this.speed,
                velY = Math.sin(this.angle) * this.speed,
                checkPointX = this.x + (Math.cos(this.angle) * 8),
                checkPointY = this.y + (Math.sin(this.angle) * 8),
                imageData = ctx.getImageData(checkPointX, checkPointY, 3, 3),
                pxlData = imageData.data,
                collision = false;

            if (checkPointX > 0 && checkPointX < width && checkPointY > 0 && checkPointY < height) {
                for (var i = 0, n = pxlData.length; i < n; i += 4) {
                    var alpha = imageData.data[i + 3];
                    if (alpha !== 0) {
                        collision = true;
                        break;
                    }
                }
            } else {
                collision = true;
            }

            if (!collision) {
                this.trapCount = 0;
                this.x += velX;
                this.y += velY;
            } else {
                this.trapCount++;
                this.angle -= 45 * (Math.PI / 180);

                if (this.trapCount >= 7) {
                    this.live = false;
                    if (traces.length < totalTraces) {
                        traces.push(new Trace());
                    }
                }

                if (Math.sqrt(dx * dx + dy * dy) > 4) {
                    this.points.push({ x: this.x, y: this.y });
                    this.lastPoint = this.points[this.points.length - 1];
                } else {
                    this.trapCount++;
                    this.x = this.lastPoint.x;
                    this.y = this.lastPoint.y;
                }
            }
        } else {
            if (Math.random() > 0.9) {
                this.live = false;
            }

            this.trapCount = 0;
            this.angle += 45 * (Math.PI / 180);

            if (Math.sqrt(dx * dx + dy * dy) > 4) {
                this.points.push({ x: this.x, y: this.y });
                this.lastPoint = this.points[this.points.length - 1];
            } else {
                this.x = this.lastPoint.x;
                this.y = this.lastPoint.y;
            }
        }
    };

    Trace.prototype.render = function() {
        ctx.beginPath();
        ctx.moveTo(this.points[0].x, this.points[0].y);

        for (var p = 1, plen = this.points.length; p < plen; p++) {
            ctx.lineTo(this.points[p].x, this.points[p].y);
        }
        ctx.lineTo(this.x, this.y);
        ctx.stroke();

        ctx.beginPath();
        ctx.arc(this.points[0].x, this.points[0].y, 2.5, 0, Math.PI * 2);
        ctx.closePath();
        ctx.fill();
        ctx.stroke();

        if (!this.live) {
            ctx.beginPath();
            ctx.arc(this.points[this.points.length - 1].x, this.points[this.points.length - 1].y, 2.5, 0, Math.PI * 2);
            ctx.closePath();
            ctx.fill();
            ctx.stroke();
        }
    };

    var traces = [];
    var startTraces = 15;
    var totalTraces = 40;

    for (var b = 0; b < startTraces; b++) {
        traces.push(new Trace());
    }

    var animationId;
    function doTrace() {
        ctx.clearRect(0, 0, width, height);

        for (var b = 0; b < traces.length; b++) {
            traces[b].render();
        }

        for (b = 0; b < traces.length; b++) {
            if (traces[b].live) {
                traces[b].update();
            }
        }

        animationId = requestAnimationFrame(doTrace);
    }

    doTrace();

    // Store animation reference for cleanup
    window._circuitAnimation = animationId;
})();

// Hide loader only when page is FULLY loaded
function hideLoader() {
    var loader = document.getElementById('page-loader');
    if (loader && !loader.classList.contains('hidden')) {
        loader.classList.add('hidden');
        setTimeout(function() {
            if (window._circuitAnimation) {
                cancelAnimationFrame(window._circuitAnimation);
            }
            loader.remove();
        }, 300);
    }
}

// Check if all images are loaded
function allImagesLoaded() {
    var images = document.querySelectorAll('img');
    for (var i = 0; i < images.length; i++) {
        if (!images[i].complete || images[i].naturalHeight === 0) {
            return false;
        }
    }
    return true;
}

// Check if fonts are loaded
function checkAndHide() {
    if (document.readyState === 'complete' && allImagesLoaded()) {
        setTimeout(hideLoader, 200);
    } else {
        setTimeout(checkAndHide, 100);
    }
}

// Wait for everything to load
window.addEventListener('load', function() {
    checkAndHide();
});

// Also check periodically in case load event already fired
if (document.readyState === 'complete') {
    checkAndHide();
}

// Fallback: hide loader after max 8 seconds
setTimeout(function() {
    hideLoader();
}, 8000);
</script>
