/**
 * Drop-in replacement for toastr's API (success/error/warning/info, plus a
 * tolerated `toastr.options = {...}` assignment), backed by SweetAlert2's
 * toast mode instead — every existing `toastr.success('...')` call site in
 * the app keeps working unchanged. Requires SweetAlert2's own script to be
 * loaded on the page before this file.
 */
(function () {
    // The app's header (navbar + breadcrumb) is a fixed 80px-tall bar at
    // the very top of every page — SweetAlert2's default top-end toast
    // position would render right underneath/behind it otherwise.
    var style = document.createElement('style');
    style.textContent = '.swal2-container.swal2-top-end { top: 92px !important; }';
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
