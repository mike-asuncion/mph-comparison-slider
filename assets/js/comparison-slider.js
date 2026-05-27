(function () {
    'use strict';

    var DRAG_THRESHOLD = 5; // px before a pointerdown becomes a drag

    function clamp(v, min, max) {
        return Math.max(min, Math.min(max, v));
    }

    function init(slider) {
        if (slider.dataset.mphCsInit) return;
        slider.dataset.mphCsInit = '1';

        var handle = slider.querySelector('.mph-cs__handle');
        if (!handle) return;

        var permalink = slider.dataset.permalink || '';
        var pointerDown = null;
        var dragged = false;

        function setPos(pct) {
            pct = clamp(pct, 0, 100);
            slider.style.setProperty('--mph-cs-pos', pct + '%');
            handle.setAttribute('aria-valuenow', String(Math.round(pct)));
            // Hide the label whose side is no longer visible
            slider.classList.toggle('mph-cs--hide-left', pct <= 5);
            slider.classList.toggle('mph-cs--hide-right', pct >= 95);
        }

        function pctFromClientX(clientX) {
            var rect = slider.getBoundingClientRect();
            if (!rect.width) return 50;
            return ((clientX - rect.left) / rect.width) * 100;
        }

        slider.addEventListener('pointerdown', function (e) {
            // Ignore right/middle clicks
            if (e.button && e.button !== 0) return;
            pointerDown = { x: e.clientX, y: e.clientY, pointerId: e.pointerId };
            dragged = false;
            try { slider.setPointerCapture(e.pointerId); } catch (_) {}
        });

        slider.addEventListener('pointermove', function (e) {
            if (!pointerDown) return;
            var dx = e.clientX - pointerDown.x;
            var dy = e.clientY - pointerDown.y;
            if (!dragged && (dx * dx + dy * dy) > (DRAG_THRESHOLD * DRAG_THRESHOLD)) {
                dragged = true;
                slider.classList.add('mph-cs--dragging');
            }
            if (dragged) {
                e.preventDefault();
                setPos(pctFromClientX(e.clientX));
            }
        });

        function endDrag(e) {
            if (!pointerDown) return;
            try { slider.releasePointerCapture(pointerDown.pointerId); } catch (_) {}
            var wasDragged = dragged;
            pointerDown = null;
            slider.classList.remove('mph-cs--dragging');
            if (!wasDragged && permalink && e.type === 'pointerup') {
                window.location.href = permalink;
            }
            // Reset dragged after click would have fired
            setTimeout(function () { dragged = false; }, 0);
        }
        slider.addEventListener('pointerup', endDrag);
        slider.addEventListener('pointercancel', endDrag);

        // Block the handle button from firing a synthetic click (we own click semantics)
        handle.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
        });

        // Keyboard accessibility on the handle
        handle.addEventListener('keydown', function (e) {
            var cur = parseFloat(slider.style.getPropertyValue('--mph-cs-pos')) || 50;
            var next = cur;
            switch (e.key) {
                case 'ArrowLeft':  next = cur - 5; break;
                case 'ArrowRight': next = cur + 5; break;
                case 'Home':       next = 0; break;
                case 'End':        next = 100; break;
                case 'Enter':
                case ' ':
                    if (permalink) {
                        e.preventDefault();
                        window.location.href = permalink;
                    }
                    return;
                default: return;
            }
            e.preventDefault();
            setPos(next);
        });

        setPos(50);
    }

    function initAll(root) {
        (root || document).querySelectorAll('.mph-cs[data-mph-cs]').forEach(init);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () { initAll(); });
    } else {
        initAll();
    }

    // Public hook so Swup / SPA navigation can re-init after content swaps
    window.mphCsInit = initAll;
})();
