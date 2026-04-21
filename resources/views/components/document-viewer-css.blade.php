{{-- Document Viewer CSS — include in @push('styles') --}}
<style>
.doc-viewer { position: relative; background: #f0f0f0; border-radius: 8px; padding: 1.5rem 1.5rem 0; margin-bottom: 1rem; }
.doc-viewer__page { background: #fff; box-shadow: 0 2px 8px rgba(0,0,0,0.1); border-radius: 4px; padding: 2.5rem 2rem; height: 500px; max-height: 500px; overflow: hidden !important; position: relative; line-height: 1.8; font-size: 0.95rem; word-wrap: break-word; overflow-wrap: break-word; }
.doc-viewer__page img { max-width: 100% !important; height: auto !important; }
.doc-viewer__page * { max-width: 100% !important; box-sizing: border-box; }
.doc-viewer__page table { table-layout: fixed; word-wrap: break-word; }
.doc-viewer__page--scrollable { overflow-y: auto !important; overflow-x: hidden !important; scroll-behavior: smooth; }
.doc-viewer__page h1, .doc-viewer__page h2, .doc-viewer__page h3 { margin-top: 0.8em; margin-bottom: 0.4em; color: #1a1a1a; }
.doc-viewer__page table { width: 100%; border-collapse: collapse; margin: 1rem 0; }
.doc-viewer__page table th, .doc-viewer__page table td { border: 1px solid #dee2e6; padding: 0.5rem 0.75rem; font-size: 0.9rem; }
.doc-viewer__page table th { background: #f8f9fa; font-weight: 600; }
.doc-viewer__page ul, .doc-viewer__page ol { padding-left: 1.5rem; }
.doc-viewer__page p { margin-bottom: 0.75rem; }
.doc-viewer__nav { display: flex; align-items: center; justify-content: center; gap: 1rem; padding: 0.75rem 0; user-select: none; }
.doc-viewer__nav-btn { width: 36px; height: 36px; border-radius: 50%; border: 1px solid #dee2e6; background: #fff; color: #495057; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: all 0.15s; font-size: 0.85rem; }
.doc-viewer__nav-btn:hover:not(:disabled) { background: #e9ecef; border-color: #adb5bd; }
.doc-viewer__nav-btn:disabled { opacity: 0.35; cursor: default; }
.doc-viewer__page-info { font-size: 0.85rem; color: #6c757d; min-width: 90px; text-align: center; }
.doc-viewer__fade-bottom { position: absolute; bottom: 0; left: 0; right: 0; height: 40px; background: rgba(255,255,255,0.9); pointer-events: none; border-radius: 0 0 4px 4px; }
.doc-viewer__logical-page { padding: 0.5rem 0; }
.doc-viewer__logical-page table { width: 100%; border-collapse: collapse; }
.doc-viewer__logical-page table th, .doc-viewer__logical-page table td { border: 1px solid #dee2e6; padding: 0.4rem 0.6rem; font-size: 0.85rem; }
.doc-viewer__logical-page table th { background: #f8f9fa; font-weight: 600; }
</style>
