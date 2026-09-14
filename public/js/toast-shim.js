/**
 * Drop-in replacement for toastr's API (success/error/warning/info, plus a
 * tolerated `toastr.options = {...}` assignment), backed by SweetAlert2's
 * toast mode instead — every existing `toastr.success('...')` call site in
 * the app keeps working unchanged. Requires SweetAlert2's own script to be
 * loaded on the page before this file.
 */
(function () {
    // Sits inside the app's 52px navbar row itself (not below the
    // breadcrumb underneath it), and noticeably more compact than
    // SweetAlert2's own toast defaults — smaller padding/icon/font so it
    // reads as a slim inline notice rather than a full popup card.
    // Every dimension inside a SweetAlert2 toast (icon, padding, title
    // text) is sized in em relative to the popup's own font-size, so
    // shrinking that one value scales the whole thing proportionally
    // without distorting the icon's internal geometry. A `transform:
    // scale()` was tried first but SweetAlert2's own show/hide keyframes
    // also animate `transform` on this element and stomp a static value
    // back to full size once the animation finishes — font-size has no
    // such conflict.
    var style = document.createElement('style');
    style.textContent = [
        '.swal2-container.swal2-top-end { top: 6px !important; padding-right: 18px !important; }',
        // Fixed px (not em/rem) so this doesn't compound with whatever
        // root font-size the current page happens to set.
        '.swal2-popup.swal2-toast { font-size: 12px !important; padding: 6px 10px !important; }',
    ].join('');
    document.head.appendChild(style);

    var Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3500,
        timerProgressBar: true,
        didOpen: function (el) {
            el.addEventListener('mouseenter', Swal.stopTimer);
            el.addEventListener('mouseleave', Swal.resumeTimer);
        },
    });

    function fire(icon, message) {
        Toast.fire({ icon: icon, title: message });
    }

    // toastr.js itself is callable directly (toastr('message') defaults to
    // type 'info') as well as via its .success/.error/etc methods — a
    // couple of call sites in the app use the bare-call form, so the shim
    // has to support both shapes, not just the method ones.
    function toastrShim(message) { fire('info', message); }
    toastrShim.options = {};
    toastrShim.success = function (message) { fire('success', message); };
    toastrShim.error = function (message) { fire('error', message); };
    toastrShim.warning = function (message) { fire('warning', message); };
    toastrShim.info = function (message) { fire('info', message); };

    window.toastr = toastrShim;
})();
