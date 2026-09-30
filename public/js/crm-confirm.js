/*
 * openConfirmModal() — V1 foundation infrastructure.
 * ---------------------------------------------------------------------
 * Meant to eventually replace the 25+ native confirm() calls the audit
 * found (delete/deactivate/cancel flows across Contacts, Companies,
 * Deals, Roles, Master Data, WhatsApp, Purchase Orders, etc). Nothing
 * in this phase migrates those call sites — this file only builds the
 * helper they will call in a later phase.
 *
 * Builds the modal markup on demand and removes it from the DOM when
 * dismissed, so no page has to carry static modal markup just to use
 * this. Uses Bootstrap 4's own $.fn.modal (already bundled in
 * vendors/js/vendor.bundle.base.js on every page) — no new modal
 * library. Loaded after that bundle in include/footer.blade.php, the
 * same slot crm-pagination.js already uses, so $ here is always the
 * one true jQuery instance (see the Select2 script-order lesson this
 * project already learned the hard way).
 *
 * Usage:
 *   openConfirmModal({
 *     title: 'Delete this lead?',
 *     message: 'This can\'t be undone.',
 *     icon: 'fa-solid fa-trash',        // optional, defaults to a warning icon
 *     variant: 'danger',                // 'danger' | 'warning' | 'info' (default: danger)
 *     confirmText: 'Delete',            // default: 'Confirm'
 *     cancelText: 'Cancel',             // default: 'Cancel'
 *     onConfirm: function(done) { ... do the thing ..., done(); }, // call done() to close + reset loading state
 *     onCancel: function() { ... },     // optional
 *   });
 */
(function (root, factory) {
  if (typeof window !== 'undefined') {
    factory(window, window.jQuery);
  }
})(this, function (window, $) {
  'use strict';

  if (!$) {
    // Loaded on a page without jQuery — fail quietly, nothing to wire up.
    return;
  }

  var ICONS = {
    danger: 'fa-solid fa-triangle-exclamation',
    warning: 'fa-solid fa-circle-exclamation',
    info: 'fa-solid fa-circle-info',
  };

  var seq = 0;

  window.openConfirmModal = function (opts) {
    opts = opts || {};
    var variant = opts.variant === 'info' || opts.variant === 'warning' ? opts.variant : 'danger';
    var id = 'crmConfirmModal' + (++seq);
    var icon = opts.icon || ICONS[variant];

    var $modal = $(
      '<div class="modal fade crm-modal crm-confirm-modal is-' + variant + '" id="' + id + '" tabindex="-1" role="dialog" aria-hidden="true">' +
        '<div class="modal-dialog" role="document">' +
          '<div class="modal-content">' +
            '<div class="modal-header">' +
              '<div class="crm-modal__icon"><i class="' + icon + '"></i></div>' +
              '<div class="crm-modal__heading">' +
                '<h5 class="modal-title"></h5>' +
                '<p class="crm-modal__subtitle"></p>' +
              '</div>' +
              '<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>' +
            '</div>' +
            '<div class="modal-footer">' +
              '<button type="button" class="crm-btn crm-btn--secondary crm-btn--sm" data-action="cancel"></button>' +
              '<button type="button" class="crm-btn crm-btn--sm" data-action="confirm"></button>' +
            '</div>' +
          '</div>' +
        '</div>' +
      '</div>'
    );

    $modal.find('.modal-title').text(opts.title || 'Are you sure?');
    $modal.find('.crm-modal__subtitle').text(opts.message || '');
    $modal.find('[data-action="cancel"]').text(opts.cancelText || 'Cancel');
    var $confirmBtn = $modal.find('[data-action="confirm"]')
      .text(opts.confirmText || 'Confirm')
      .addClass(variant === 'danger' ? 'crm-btn--danger' : 'crm-btn--primary');

    $('body').append($modal);

    function cleanup() {
      $modal.modal('hide');
    }

    $modal.find('[data-action="cancel"]').on('click', function () {
      if (typeof opts.onCancel === 'function') opts.onCancel();
      cleanup();
    });

    $confirmBtn.on('click', function () {
      if (typeof opts.onConfirm !== 'function') {
        cleanup();
        return;
      }
      var $footer = $modal.find('.modal-footer');
      $footer.addClass('is-loading');
      $confirmBtn.prop('disabled', true);
      opts.onConfirm(function done() {
        $footer.removeClass('is-loading');
        $confirmBtn.prop('disabled', false);
        cleanup();
      });
    });

    $modal.on('hidden.bs.modal', function () {
      $modal.remove();
    });

    if (typeof $modal.modal === 'function') {
      $modal.modal('show');
    }
  };
});
