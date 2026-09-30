{{--
  Shared empty state — replaces the raw "Loading…" / "No data found"
  text found on Leads, Deals, Companies, Contacts, Orders, Audit Log,
  Roles, Master Data and Projects. New, additive infrastructure for
  V1 — no existing page includes this yet.

  Usage:

    @component('include.empty-state', [
        'icon' => 'fa-solid fa-inbox',
        'title' => 'No leads yet',
        'description' => 'Leads you create or import will show up here.',
    ])
        @slot('actions')
            <button class="crm-btn crm-btn--primary crm-btn--sm">Create a lead</button>
        @endslot
    @endcomponent

  For a loading (not empty) state, use the same markup with a spinner
  icon, e.g. icon => 'fa-solid fa-spinner fa-spin', title => 'Loading…'.
--}}
<div class="crm-empty">
  @isset($icon)
    <div class="crm-empty__icon"><i class="{{ $icon }}"></i></div>
  @endisset
  <p class="crm-empty__title">{{ $title }}</p>
  @isset($description)
    <p class="crm-empty__desc">{{ $description }}</p>
  @endisset
  @isset($actions)
    {{ $actions }}
  @endisset
</div>
