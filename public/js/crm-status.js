/*
 * renderStatusBadge() — V1 foundation infrastructure.
 * ---------------------------------------------------------------------
 * One status -> color map per entity, feeding the single .crm-badge
 * component (public/css/crm-components.css) instead of the six
 * different badge mechanisms the audit found (Bootstrap badge-*,
 * .badge-pill, .status-badge, .status-pill, .payment-badge, plain
 * colored text). Nothing in this phase rewires an existing page's
 * rendering to call this — that's a later, per-module migration.
 * The maps below are a starting point transcribed from each module's
 * current values (lead.blade.php's status hex map, deals/show.blade.php's
 * statusBadgeClass(), orders' payClass()/payBadge()) so the *meaning*
 * of each status doesn't silently change when a page migrates onto it
 * — only its rendering does.
 */
(function (window) {
  'use strict';

  var CRM_STATUS_MAP = {
    /* V2 correction: the placeholder map V1 shipped didn't match the
       real values this app actually uses (found while migrating
       admin/lead/lead.blade.php's own LEAD_STATUS_COLORS in V2) —
       replaced with the real vocabulary rather than the guessed one. */
    lead: {
      new: { variant: 'info', label: 'New' },
      contacted: { variant: 'info', label: 'Contacted' },
      interested: { variant: 'info', label: 'Interested' },
      qualified: { variant: 'info', label: 'Qualified' },
      warm: { variant: 'warning', label: 'Warm' },
      follow_up: { variant: 'warning', label: 'Follow Up' },
      hot: { variant: 'danger', label: 'Hot' },
      cold: { variant: 'neutral', label: 'Cold' },
      converted: { variant: 'success', label: 'Converted' },
      not_interested: { variant: 'neutral', label: 'Not Interested' },
      closed: { variant: 'neutral', label: 'Closed' },
    },
    deal: {
      open: { variant: 'info', label: 'Open' },
      won: { variant: 'success', label: 'Won' },
      lost: { variant: 'neutral', label: 'Lost' },
    },
    /* V2 correction: replaced with the real order_status vocabulary
       found migrating orders/index.blade.php's own status-${token} map. */
    order: {
      new: { variant: 'info', label: 'New' },
      approved: { variant: 'success', label: 'Approved' },
      in_progress: { variant: 'info', label: 'In Progress' },
      on_hold: { variant: 'neutral', label: 'On Hold' },
      delivered: { variant: 'success', label: 'Delivered' },
      closed: { variant: 'neutral', label: 'Closed' },
      cancelled: { variant: 'danger', label: 'Cancelled' },
    },
    payment: {
      pending: { variant: 'danger', label: 'Pending' },
      unpaid: { variant: 'danger', label: 'Unpaid' },
      partial: { variant: 'warning', label: 'Partially paid' },
      paid: { variant: 'success', label: 'Paid' },
    },
    task: {
      open: { variant: 'info', label: 'Open' },
      blocked: { variant: 'neutral', label: 'Blocked' },
      done: { variant: 'success', label: 'Done' },
    },
    priority: {
      low: { variant: 'neutral', label: 'Low' },
      medium: { variant: 'info', label: 'Medium' },
      high: { variant: 'warning', label: 'High' },
      urgent: { variant: 'danger', label: 'Urgent' },
    },
  };

  function normalize(value) {
    return String(value == null ? '' : value).trim().toLowerCase().replace(/\s+/g, '_');
  }

  /** Returns {variant, label} for an entity/value pair, falling back to neutral. */
  function statusInfo(entity, value) {
    var map = CRM_STATUS_MAP[entity];
    var key = normalize(value);
    var entry = map && map[key];
    if (entry) return entry;
    return { variant: 'neutral', label: value == null || value === '' ? 'Unknown' : String(value) };
  }

  /** Returns an escaped <span class="crm-badge ..."> HTML string. */
  function renderStatusBadge(entity, value) {
    var info = statusInfo(entity, value);
    var label = String(info.label).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
    return '<span class="crm-badge crm-badge--' + info.variant + '"><span class="crm-badge__dot"></span>' + label + '</span>';
  }

  window.CRM_STATUS_MAP = CRM_STATUS_MAP;
  window.renderStatusBadge = renderStatusBadge;
  window.crmStatusInfo = statusInfo;
})(window);
