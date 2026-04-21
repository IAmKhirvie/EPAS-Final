{{-- Document Viewer JS — include in @push('scripts') when document_content is present --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    var page = document.getElementById('docPage');
    if (!page) return;
    var content = document.getElementById('docContent');
    var fade = document.getElementById('docFade');
    var nav = document.getElementById('docNav');
    var prevBtn = document.getElementById('docPrev');
    var nextBtn = document.getElementById('docNext');
    var pageInfo = document.getElementById('docPageInfo');
    var currentPage = 1;

    var logicalPages = content.querySelectorAll('.doc-viewer__logical-page');
    var useLogicalPages = logicalPages.length > 1;

    if (useLogicalPages) {
        var totalPages = logicalPages.length;
        fade.style.display = 'none';
        page.style.overflowY = 'auto';
        page.style.overflowX = 'hidden';
        page.style.scrollbarWidth = 'thin';

        function showLogicalPage() {
            logicalPages.forEach(function(lp, i) {
                lp.style.display = (i === currentPage - 1) ? 'block' : 'none';
            });
            if (totalPages <= 1) { nav.style.display = 'none'; return; }
            nav.style.display = 'flex';
            pageInfo.textContent = 'Page ' + currentPage + ' of ' + totalPages;
            prevBtn.disabled = currentPage <= 1;
            nextBtn.disabled = currentPage >= totalPages;
            page.scrollTop = 0;
        }
        showLogicalPage();
        prevBtn.addEventListener('click', function() { if (currentPage > 1) { currentPage--; showLogicalPage(); } });
        nextBtn.addEventListener('click', function() { if (currentPage < totalPages) { currentPage++; showLogicalPage(); } });
        document.addEventListener('keydown', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') return;
            if (e.key === 'ArrowLeft' && currentPage > 1) { currentPage--; showLogicalPage(); }
            if (e.key === 'ArrowRight' && currentPage < totalPages) { currentPage++; showLogicalPage(); }
        });
    } else {
        var PAGE_HEIGHT, totalHeight, totalPages;
        function update() {
            totalHeight = content.scrollHeight;
            PAGE_HEIGHT = page.clientHeight;
            totalPages = Math.max(1, Math.ceil(totalHeight / PAGE_HEIGHT));
            if (totalPages <= 1) { nav.style.display = 'none'; fade.style.display = 'none'; return; }
            nav.style.display = 'flex';
            pageInfo.textContent = 'Page ' + currentPage + ' of ' + totalPages;
            prevBtn.disabled = currentPage <= 1;
            nextBtn.disabled = currentPage >= totalPages;
            page.scrollTop = (currentPage - 1) * PAGE_HEIGHT;
            fade.style.display = currentPage < totalPages ? 'block' : 'none';
        }
        page.classList.add('doc-viewer__page--scrollable');
        page.style.scrollbarWidth = 'none';
        var s = document.createElement('style');
        s.textContent = '#docPage::-webkit-scrollbar{display:none}';
        document.head.appendChild(s);
        prevBtn.addEventListener('click', function() { if (currentPage > 1) { currentPage--; update(); } });
        nextBtn.addEventListener('click', function() { if (currentPage < totalPages) { currentPage++; update(); } });
        document.addEventListener('keydown', function(e) {
            if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'SELECT') return;
            if (e.key === 'ArrowLeft' && currentPage > 1) { currentPage--; update(); }
            if (e.key === 'ArrowRight' && currentPage < totalPages) { currentPage++; update(); }
        });
        update();
        window.addEventListener('resize', update);
    }
});
</script>
