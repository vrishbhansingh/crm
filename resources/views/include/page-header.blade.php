{{--
  Shared page header — the canonical replacement for the ~21 hand-
  copied ".crm-page-header" blocks the audit found (each with its own
  drifting radius/padding). This partial is new, additive infrastructure
  for V1: nothing existing includes it yet, so it changes no page until
  a later migration phase swaps a page's own header markup for this.

  Usage (classic Blade @component/@slot — no anonymous component
  registration needed, works in this app's existing Blade setup):

    @component('include.page-header', [
        'icon' => 'fa-solid fa-users',
        'title' => 'Leads',
        'subtitle' => 'Every inbound lead across your team.',
        'breadcrumb' => 'Sales / Leads',
    ])
        @slot('actions')
            <button class="crm-btn crm-btn--primary"><i class="fa-solid fa-plus"></i> New lead</button>
        @endslot
        @slot('secondary')
            <button class="crm-btn crm-btn--secondary crm-btn--sm">Import</button>
        @endslot
    @endcomponent

  All slots except icon/title are optional.
--}}
<div class="crm-header">
  <div class="crm-header__main">
    @isset($icon)
      <div class="crm-header__icon"><i class="{{ $icon }}"></i></div>
    @endisset
    <div class="crm-header__text">
      @isset($breadcrumb)
        <div class="crm-header__breadcrumb">{{ $breadcrumb }}</div>
      @endisset
      <h1 class="crm-header__title">{{ $title }}</h1>
      @isset($subtitle)
        <p class="crm-header__subtitle">{{ $subtitle }}</p>
      @endisset
      @isset($secondary)
        <div class="crm-header__secondary">{{ $secondary }}</div>
      @endisset
    </div>
  </div>
  @isset($actions)
    <div class="crm-header__actions">{{ $actions }}</div>
  @endisset
</div>
