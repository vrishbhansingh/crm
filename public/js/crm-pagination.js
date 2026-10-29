/**
 * Shared pagination widget for CRM list pages. Renders a "Showing X-Y of Z"
 * summary + Bootstrap pager against a Laravel-style paginator meta object
 * ({current_page, last_page, total, per_page}), and calls back with the
 * requested page number on click. No fetching/rendering opinions beyond
 * that — each page keeps its own row-rendering logic.
 *
 * Optional 4th argument { perPageOptions: [25, 50, 100, 200], onPerPage(n) }
 * adds a "Rows" picker, so a page with thousands of records can show more at
 * once. It stays visible even when everything fits on one page, so the choice
 * can always be changed back. Pages that don't pass it behave exactly as before.
 */
function renderCrmPagination(container, meta, onPageChange, options) {
    var el = typeof container === 'string' ? document.querySelector(container) : container;
    if (!el) return;

    var picker = options && options.perPageOptions && options.onPerPage;

    if (!meta || (meta.last_page <= 1 && !(picker && meta && meta.total > options.perPageOptions[0]))) {
        el.innerHTML = '';
        return;
    }

    var currentPage = meta.current_page;
    var lastPage = meta.last_page;
    var total = meta.total;
    var perPage = meta.per_page;
    var from = total === 0 ? 0 : (currentPage - 1) * perPage + 1;
    var to = Math.min(currentPage * perPage, total);

    function item(label, page, disabled, active) {
        return '<li class="page-item ' + (disabled ? 'disabled' : '') + ' ' + (active ? 'active' : '') + '">'
            + '<a href="#" class="page-link" data-page="' + page + '">' + label + '</a></li>';
    }

    var pages = '';
    var start = Math.max(1, currentPage - 2);
    var end = Math.min(lastPage, currentPage + 2);
    for (var i = start; i <= end; i++) pages += item(i, i, false, i === currentPage);

    var rows = '';
    if (picker) {
        rows = '<label class="mb-0 text-muted" style="font-size:12.5px;display:flex;align-items:center;gap:6px;">Rows '
            + '<select class="crm-perpage form-control form-control-sm" style="width:auto;display:inline-block;">'
            + options.perPageOptions.map(function (n) {
                return '<option value="' + n + '"' + (n === perPage ? ' selected' : '') + '>' + n + '</option>';
            }).join('')
            + '</select></label>';
    }

    el.innerHTML = '<div class="d-flex justify-content-between align-items-center flex-wrap" style="gap:10px;">'
        + '<div class="d-flex align-items-center flex-wrap" style="gap:14px;"><small class="text-muted">Showing ' + from + '-' + to + ' of ' + total + '</small>' + rows + '</div>'
        + '<nav aria-label="Pagination"><ul class="pagination pagination-sm mb-0">'
        + item('&laquo;', currentPage - 1, currentPage <= 1, false)
        + pages
        + item('&raquo;', currentPage + 1, currentPage >= lastPage, false)
        + '</ul></nav></div>';

    el.querySelectorAll('.page-link').forEach(function (a) {
        a.addEventListener('click', function (e) {
            e.preventDefault();
            var page = parseInt(this.dataset.page, 10);
            if (!isNaN(page) && page >= 1 && page <= lastPage && page !== currentPage) {
                onPageChange(page);
            }
        });
    });

    var select = el.querySelector('.crm-perpage');
    if (select) {
        select.addEventListener('change', function () {
            options.onPerPage(parseInt(this.value, 10));
        });
    }
}
