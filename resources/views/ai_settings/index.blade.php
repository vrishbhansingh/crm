<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>AI Assistant Settings | CRM</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.css">

    <style>
        .aiset { max-width: 1040px; margin: 0 auto; }

        /* ---- Hero ---- */
        .aiset-hero { display: flex; flex-wrap: wrap; gap: 16px; align-items: center; justify-content: space-between;
            background: var(--crm-surface, #fff); border: 1px solid var(--crm-border, #dcdfd8); border-radius: 18px; padding: 22px 24px; margin-bottom: 14px; }
        .aiset-hero h4 { margin: 0 0 4px; font-size: 21px; font-weight: 700; color: var(--crm-ink, #171a17); display: flex; align-items: center; gap: 10px; }
        .aiset-hero h4 i { color: var(--crm-accent, #1f6f63); font-size: 18px; }
        .aiset-hero p { margin: 0; font-size: 13.5px; color: var(--crm-ink-muted, #585f57); max-width: 600px; line-height: 1.55; }
        .aiset-chips { display: flex; flex-wrap: wrap; gap: 8px; margin-top: 12px; }
        .aiset-chip { display: inline-flex; align-items: center; gap: 6px; font-size: 12px; font-weight: 600; padding: 4px 11px; border-radius: 999px; background: var(--crm-accent-tint, #e4efec); color: var(--crm-accent-dark, #154f46); }

        .aiset-btn { display: inline-flex; align-items: center; justify-content: center; gap: 8px; min-height: 44px; padding: 0 20px; border: 0; border-radius: 12px; font-size: 14px; font-weight: 600; cursor: pointer;
            background: var(--crm-accent, #1f6f63); color: #fff; transition: background .15s ease, transform .15s ease, box-shadow .15s ease; text-decoration: none; }
        .aiset-btn:hover { background: var(--crm-accent-dark, #154f46); color: #fff; transform: translateY(-1px); box-shadow: 0 6px 16px rgba(31,111,99,.25); }
        .aiset-btn:focus-visible, .aiset-ghost:focus-visible, .aiset-tile:focus-visible, .aiset-model:focus-visible { outline: 3px solid color-mix(in srgb, var(--crm-accent, #1f6f63) 40%, transparent); outline-offset: 2px; }
        .aiset-btn:disabled { opacity: .6; cursor: default; transform: none; box-shadow: none; }
        [data-theme="dark"] .aiset-btn, [data-theme="dark"] .aiset-btn:hover { color: #12151a; }
        .aiset-ghost { display: inline-flex; align-items: center; justify-content: center; gap: 6px; min-height: 38px; min-width: 38px; padding: 0 12px; border-radius: 10px; font-size: 13px; font-weight: 600; cursor: pointer;
            border: 1px solid var(--crm-border-strong, #c3c8bd); background: var(--crm-surface, #fff); color: var(--crm-ink, #171a17); transition: background .15s ease, border-color .15s ease; text-decoration: none; }
        .aiset-ghost:hover { background: var(--crm-surface-2, #ecefe9); color: var(--crm-ink, #171a17); }
        .aiset-ghost.danger:hover { background: var(--crm-danger-tint, #fae8e7); border-color: var(--crm-danger, #b3261e); color: var(--crm-danger, #b3261e); }
        .aiset-ghost:disabled { opacity: .45; cursor: default; }

        /* ---- Notices ---- */
        .aiset-warn { display: flex; gap: 12px; align-items: flex-start; padding: 14px 16px; border-radius: 14px; margin-bottom: 14px; font-size: 13px; line-height: 1.55;
            background: var(--crm-warning-tint, #fbf0e1); color: var(--crm-ink, #171a17); border: 1px solid color-mix(in srgb, var(--crm-warning, #a6650a) 35%, transparent); }
        .aiset-warn > i { color: var(--crm-warning, #a6650a); margin-top: 3px; }
        .aiset-warn strong { display: block; margin-bottom: 2px; }
        .aiset-info { display: flex; gap: 12px; padding: 12px 16px; border-radius: 14px; margin-bottom: 18px; font-size: 13px; line-height: 1.55;
            background: var(--crm-surface-2, #ecefe9); color: var(--crm-ink, #171a17); }
        .aiset-info > i { color: var(--crm-accent, #1f6f63); margin-top: 3px; }

        /* ---- Configuration cards ---- */
        .aiset-card { display: grid; grid-template-columns: auto 1fr auto; gap: 16px; align-items: center; background: var(--crm-surface, #fff);
            border: 1px solid var(--crm-border, #dcdfd8); border-radius: 16px; padding: 16px 18px; margin-bottom: 12px; transition: box-shadow .15s ease, border-color .15s ease; }
        .aiset-card:hover { box-shadow: 0 8px 22px rgba(15,23,42,.07); border-color: var(--crm-border-strong, #c3c8bd); }
        .aiset-card.off { opacity: .6; }
        .aiset-badge { width: 46px; height: 46px; border-radius: 14px; display: flex; align-items: center; justify-content: center; color: #fff; font-weight: 700; font-size: 19px; flex-shrink: 0; }
        .aiset-title { display: flex; flex-wrap: wrap; align-items: center; gap: 8px; font-weight: 700; font-size: 15.5px; color: var(--crm-ink, #171a17); }
        .aiset-rank { font-size: 11px; font-weight: 700; padding: 2px 9px; border-radius: 999px; background: var(--crm-accent, #1f6f63); color: #fff; }
        .aiset-rank.backup { background: var(--crm-neutral-tint, #eceee9); color: var(--crm-neutral, #585f57); }
        [data-theme="dark"] .aiset-rank:not(.backup) { color: #12151a; }
        .aiset-sub { font-size: 12.5px; color: var(--crm-ink-muted, #585f57); margin-top: 3px; display: flex; flex-wrap: wrap; gap: 4px 12px; }
        .aiset-sub i { margin-right: 4px; opacity: .75; }
        .aiset-use { font-size: 12.5px; color: var(--crm-ink-muted, #585f57); margin-top: 6px; }
        .aiset-use strong { color: var(--crm-ink, #171a17); }
        .aiset-err { font-size: 12.5px; margin-top: 6px; color: var(--crm-danger, #b3261e); display: flex; gap: 6px; align-items: flex-start; }
        .aiset-pill { font-size: 11.5px; font-weight: 600; padding: 2px 10px; border-radius: 999px; }
        .aiset-pill.ok { background: var(--crm-success-tint, #e5f3ec); color: var(--crm-success, #1f7a4d); }
        .aiset-pill.failing { background: var(--crm-danger-tint, #fae8e7); color: var(--crm-danger, #b3261e); }
        .aiset-pill.untested, .aiset-pill.off { background: var(--crm-neutral-tint, #eceee9); color: var(--crm-neutral, #585f57); }
        .aiset-tier { font-size: 11px; font-weight: 600; padding: 1px 8px; border-radius: 6px; background: var(--crm-info-tint, #e7eef5); color: var(--crm-info, #2a5c8a); }
        .aiset-actions { display: flex; flex-wrap: wrap; gap: 6px; justify-content: flex-end; }
        .aiset-empty { text-align: center; padding: 44px 20px; background: var(--crm-surface, #fff); border: 1.5px dashed var(--crm-border-strong, #c3c8bd); border-radius: 18px; color: var(--crm-ink-muted, #585f57); }
        .aiset-empty .big { width: 64px; height: 64px; margin: 0 auto 14px; border-radius: 20px; background: var(--crm-accent-tint, #e4efec); color: var(--crm-accent, #1f6f63); display: flex; align-items: center; justify-content: center; font-size: 26px; }
        .aiset-empty h5 { color: var(--crm-ink, #171a17); font-weight: 700; margin-bottom: 6px; }

        /* ---- Drawer ---- */
        .aiset-overlay { position: fixed; inset: 0; background: rgba(15,20,18,.45); z-index: 1050; opacity: 0; pointer-events: none; transition: opacity .2s ease; }
        .aiset-overlay.open { opacity: 1; pointer-events: auto; }
        .aiset-drawer { position: fixed; top: 0; right: 0; bottom: 0; width: 560px; max-width: 100vw; background: var(--crm-surface, #fff); z-index: 1051; display: flex; flex-direction: column;
            transform: translateX(100%); transition: transform .25s cubic-bezier(.2,.8,.2,1); box-shadow: -16px 0 40px rgba(0,0,0,.18); }
        .aiset-drawer.open { transform: translateX(0); }
        .aiset-dh { display: flex; align-items: center; justify-content: space-between; padding: 18px 22px; border-bottom: 1px solid var(--crm-border, #dcdfd8); }
        .aiset-dh h5 { margin: 0; font-weight: 700; font-size: 17px; color: var(--crm-ink, #171a17); }
        .aiset-x { width: 40px; height: 40px; border-radius: 10px; border: 0; background: transparent; color: var(--crm-ink-muted, #585f57); font-size: 16px; cursor: pointer; }
        .aiset-x:hover { background: var(--crm-surface-2, #ecefe9); }
        .aiset-db { flex: 1; overflow-y: auto; padding: 8px 22px 22px; }
        .aiset-df { display: flex; gap: 10px; justify-content: flex-end; padding: 14px 22px; border-top: 1px solid var(--crm-border, #dcdfd8); background: var(--crm-surface, #fff); }
        .aiset-step { padding: 18px 0 4px; }
        .aiset-step > h6 { display: flex; align-items: center; gap: 10px; font-size: 14px; font-weight: 700; color: var(--crm-ink, #171a17); margin: 0 0 12px; }
        .aiset-step > h6 .n { width: 24px; height: 24px; border-radius: 50%; background: var(--crm-accent, #1f6f63); color: #fff; font-size: 12px; display: inline-flex; align-items: center; justify-content: center; }
        [data-theme="dark"] .aiset-step > h6 .n { color: #12151a; }
        .aiset-step.locked { opacity: .45; pointer-events: none; }

        .aiset-tiles { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .aiset-tile { display: flex; gap: 12px; align-items: center; text-align: left; padding: 12px 14px; border-radius: 14px; cursor: pointer; background: var(--crm-surface, #fff);
            border: 1.5px solid var(--crm-border, #dcdfd8); transition: border-color .15s ease, background .15s ease, transform .15s ease; min-height: 64px; }
        .aiset-tile:hover { border-color: var(--crm-border-strong, #c3c8bd); transform: translateY(-1px); }
        .aiset-tile.sel { border-color: var(--crm-accent, #1f6f63); background: var(--crm-accent-tint, #e4efec); }
        .aiset-tile .aiset-badge { width: 38px; height: 38px; font-size: 16px; border-radius: 11px; }
        .aiset-tile b { display: block; font-size: 13.5px; color: var(--crm-ink, #171a17); }
        .aiset-tile small { display: block; font-size: 11.5px; color: var(--crm-ink-muted, #585f57); line-height: 1.35; }
        .aiset-free { font-size: 10.5px; font-weight: 700; color: var(--crm-success, #1f7a4d); background: var(--crm-success-tint, #e5f3ec); padding: 1px 7px; border-radius: 999px; margin-left: 6px; }

        .aiset-getkey { display: flex; flex-wrap: wrap; gap: 12px; align-items: center; justify-content: space-between; padding: 12px 14px; margin-bottom: 12px; border-radius: 14px;
            background: var(--crm-surface-2, #ecefe9); font-size: 12.5px; color: var(--crm-ink-muted, #585f57); line-height: 1.5; }
        .aiset-getkey b { color: var(--crm-ink, #171a17); }
        .aiset-field { margin-bottom: 14px; }
        .aiset-field label { display: block; font-size: 12.5px; font-weight: 600; color: var(--crm-ink, #171a17); margin-bottom: 5px; }
        .aiset-field label span { font-weight: 400; color: var(--crm-ink-muted, #585f57); }
        .aiset-input { width: 100%; min-height: 44px; border-radius: 12px; border: 1.5px solid var(--crm-border-strong, #c3c8bd); padding: 0 14px; font-size: 14px; background: var(--crm-surface, #fff); color: var(--crm-ink, #171a17); transition: border-color .15s ease, box-shadow .15s ease; }
        .aiset-input:focus { outline: none; border-color: var(--crm-accent, #1f6f63); box-shadow: 0 0 0 3px color-mix(in srgb, var(--crm-accent, #1f6f63) 16%, transparent); }
        .aiset-keywrap { position: relative; }
        .aiset-keywrap .aiset-input { padding-right: 48px; }
        .aiset-eye { position: absolute; right: 4px; top: 4px; width: 36px; height: 36px; border: 0; background: transparent; border-radius: 8px; color: var(--crm-ink-muted, #585f57); cursor: pointer; }
        .aiset-hint { font-size: 12px; color: var(--crm-ink-muted, #585f57); margin-top: 5px; }

        .aiset-models { display: grid; gap: 8px; }
        .aiset-model { display: grid; grid-template-columns: auto 1fr auto; gap: 12px; align-items: center; padding: 11px 14px; border-radius: 14px; cursor: pointer; text-align: left; background: var(--crm-surface, #fff);
            border: 1.5px solid var(--crm-border, #dcdfd8); transition: border-color .15s ease, background .15s ease; }
        .aiset-model:hover { border-color: var(--crm-border-strong, #c3c8bd); }
        .aiset-model.sel { border-color: var(--crm-accent, #1f6f63); background: var(--crm-accent-tint, #e4efec); }
        .aiset-radio { width: 18px; height: 18px; border-radius: 50%; border: 2px solid var(--crm-border-strong, #c3c8bd); display: inline-block; position: relative; }
        .aiset-model.sel .aiset-radio { border-color: var(--crm-accent, #1f6f63); }
        .aiset-model.sel .aiset-radio::after { content: ''; position: absolute; inset: 3px; border-radius: 50%; background: var(--crm-accent, #1f6f63); }
        .aiset-model .nm { font-weight: 600; font-size: 13.5px; color: var(--crm-ink, #171a17); display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .aiset-model .ds { font-size: 12px; color: var(--crm-ink-muted, #585f57); margin-top: 2px; }
        .aiset-model .pr { text-align: right; font-size: 11.5px; color: var(--crm-ink-muted, #585f57); white-space: nowrap; }
        .aiset-model .pr b { display: block; font-size: 13px; color: var(--crm-ink, #171a17); }
        .aiset-note { font-size: 11.5px; color: var(--crm-ink-muted, #585f57); margin-top: 10px; line-height: 1.5; }
        .aiset-note a { color: var(--crm-accent, #1f6f63); font-weight: 600; }

        .aiset-switch { display: flex; align-items: center; gap: 10px; cursor: pointer; font-size: 13.5px; font-weight: 600; color: var(--crm-ink, #171a17); }
        .aiset-switch input { position: absolute; opacity: 0; }
        .aiset-switch .track { width: 42px; height: 24px; border-radius: 999px; background: var(--crm-border-strong, #c3c8bd); position: relative; transition: background .15s ease; flex-shrink: 0; }
        .aiset-switch .track::after { content: ''; position: absolute; top: 3px; left: 3px; width: 18px; height: 18px; border-radius: 50%; background: #fff; transition: transform .15s ease; }
        .aiset-switch input:checked + .track { background: var(--crm-accent, #1f6f63); }
        .aiset-switch input:checked + .track::after { transform: translateX(18px); }
        .aiset-switch input:focus-visible + .track { outline: 3px solid color-mix(in srgb, var(--crm-accent, #1f6f63) 40%, transparent); outline-offset: 2px; }

        @media (max-width: 720px) {
            .aiset-card { grid-template-columns: auto 1fr; }
            .aiset-actions { grid-column: 1 / -1; justify-content: flex-start; }
            .aiset-tiles { grid-template-columns: 1fr; }
            .aiset-drawer { width: 100vw; }
            .aiset-hero { padding: 18px; }
        }
        @media (prefers-reduced-motion: reduce) { .aiset-drawer, .aiset-overlay, .aiset-btn, .aiset-tile { transition: none; } }
    </style>
</head>

<body>
    <div class="container-scroller">
        @include('include.header')

        <div class="container-fluid page-body-wrapper">
            @include('include.sidebar')

            <div class="content-wrapper">
                <div class="aiset">

                    <div class="aiset-hero">
                        <div>
                            <h4><i class="fa-solid fa-wand-magic-sparkles"></i> AI Assistant</h4>
                            <p>Connect your company's own AI provider. It powers the chat assistant, lead summaries, draft replies and the WhatsApp auto-reply, and you choose the provider, the model, and what you spend.</p>
                            <div class="aiset-chips">
                                <span class="aiset-chip"><i class="fa-solid fa-lock"></i> Keys stored encrypted</span>
                                <span class="aiset-chip"><i class="fa-solid fa-shuffle"></i> Automatic backup switching</span>
                                <span class="aiset-chip"><i class="fa-solid fa-chart-simple"></i> Usage tracked per key</span>
                            </div>
                        </div>
                        <button type="button" class="aiset-btn" id="addBtn"><i class="fa-solid fa-plus"></i> Add AI provider</button>
                    </div>

                    <div class="aiset-warn" role="note">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <div>
                            <strong>AI is a third-party service and can make mistakes</strong>
                            Answers come from the provider you connect, not from this CRM, and can be wrong or incomplete. Always check important information (amounts, dates, customer details) before acting on it.
                            Your questions, and the CRM records needed to answer them, are sent to that provider, so check that their terms suit your business.
                        </div>
                    </div>

                    @if (! $enabled)
                        <div class="aiset-warn"><i class="fa-solid fa-power-off"></i><div><strong>The assistant is switched off on this server</strong>(AI_ASSISTANT_ENABLED=false). Saved keys will not be used until it is turned on.</div></div>
                    @endif

                    <div class="aiset-info">
                        <i class="fa-solid fa-circle-info"></i>
                        <div>
                            Providers are used from the top down. If a key expires, runs out of credit, hits its limit or its model is unavailable, the assistant moves to the next one by itself and emails the alert address you set.
                            A failed key is tried again after 15 minutes. You can mix providers, for example a free Gemini key first and a Claude key as backup.
                            @if ($serverDefault)
                                <br>This server also has a default key, used only by companies that have saved none of their own.
                            @endif
                        </div>
                    </div>

                    <div id="list"></div>
                </div>

                @include('include.footer')
            </div>
        </div>
    </div>

    {{-- Add / edit drawer --}}
    <div class="aiset-overlay" id="drawerOverlay"></div>
    <aside class="aiset-drawer" id="drawer" role="dialog" aria-modal="true" aria-labelledby="drawerTitle" aria-hidden="true">
        <div class="aiset-dh">
            <h5 id="drawerTitle">Add AI provider</h5>
            <button type="button" class="aiset-x" id="drawerClose" aria-label="Close"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="cfgForm" autocomplete="off" style="display:flex;flex-direction:column;flex:1;min-height:0;">
            <div class="aiset-db">
                <input type="hidden" id="cfgId">

                <div class="aiset-step">
                    <h6><span class="n">1</span> Choose a provider</h6>
                    <div class="aiset-tiles" id="tiles"></div>
                </div>

                <div class="aiset-step locked" id="stepKey">
                    <h6><span class="n">2</span> Add your API key</h6>
                    <div class="aiset-getkey">
                        <div id="getKeyText">Choose a provider first.</div>
                        <a id="getKeyBtn" class="aiset-ghost" href="#" target="_blank" rel="noopener noreferrer" style="display:none;"><i class="fa-solid fa-arrow-up-right-from-square"></i> Get API key</a>
                    </div>
                    <div class="aiset-field">
                        <label for="cfgKey">API key</label>
                        <div class="aiset-keywrap">
                            <input type="password" id="cfgKey" class="aiset-input" autocomplete="new-password" spellcheck="false" placeholder="Paste your key here">
                            <button type="button" class="aiset-eye" id="eyeBtn" aria-label="Show or hide key"><i class="fa-regular fa-eye"></i></button>
                        </div>
                        <div class="aiset-hint" id="cfgKeyHint">Stored encrypted and never shown again.</div>
                    </div>
                </div>

                <div class="aiset-step locked" id="stepModel">
                    <h6><span class="n">3</span> Pick a model <span style="font-weight:400;color:var(--crm-ink-muted);font-size:12px;">(this decides what you spend)</span></h6>
                    <div class="aiset-models" id="models"></div>
                    <div class="aiset-field" id="customModelWrap" style="display:none;margin-top:10px;">
                        <label for="cfgModel">Model id</label>
                        <input type="text" id="cfgModel" class="aiset-input" maxlength="80" placeholder="e.g. openai/gpt-4o-mini">
                        <div class="aiset-hint">Exactly as the provider names it. Press Test after saving to check it.</div>
                    </div>
                    <div class="aiset-note" id="priceNote"></div>
                </div>

                <div class="aiset-step locked" id="stepDetails">
                    <h6><span class="n">4</span> Details</h6>
                    <div class="aiset-field">
                        <label for="cfgName">Name</label>
                        <input type="text" id="cfgName" class="aiset-input" maxlength="100" placeholder="e.g. Main key">
                    </div>
                    <div class="aiset-field">
                        <label for="cfgEmail">Alert email <span>(optional)</span></label>
                        <input type="email" id="cfgEmail" class="aiset-input" maxlength="190" placeholder="Who should be told if this key stops working">
                    </div>
                    <label class="aiset-switch"><input type="checkbox" id="cfgActive" checked><span class="track"></span> Active (used by the assistant)</label>
                </div>
            </div>
            <div class="aiset-df">
                <button type="button" class="aiset-ghost" id="drawerCancel">Cancel</button>
                <button type="submit" class="aiset-btn" id="cfgSave"><i class="fa-solid fa-check"></i> <span id="cfgSaveText">Save &amp; test</span></button>
            </div>
        </form>
    </div>

    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert2/11.26.25/sweetalert2.min.js"></script>
    <script src="{{ asset('js/toast-shim.js') }}?v={{ filemtime(public_path('js/toast-shim.js')) }}"></script>
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        const base = `{{ url('settings/ai') }}`;
        const PROVIDERS = @json($providers);
        const PRICED_AT = @json($pricedAt);
        const SAMPLE_IN = {{ (int) $sampleIn }};
        const SAMPLE_OUT = {{ (int) $sampleOut }};
        const byKey = Object.fromEntries(PROVIDERS.map(p => [p.key, p]));
        const label = { ok: 'Working', failing: 'Failing', untested: 'Not tested yet' };

        let rows = [];
        let sel = { provider: null, model: '', custom: false, editing: null };

        const esc = v => $('<div>').text(v ?? '').html();
        const money = v => v === null || v === undefined ? null : (v < 0.01 && v > 0 ? '<$0.01' : '$' + v.toFixed(v >= 100 ? 0 : 2));
        const tokens = n => n >= 1e6 ? (n / 1e6).toFixed(1) + 'M' : n >= 1e3 ? (n / 1e3).toFixed(1) + 'k' : String(n);
        const priceLine = m => m.in === null ? 'Check provider pricing' : `$${m.in} in &middot; $${m.out} out`;

        /* ------------------------------ list ------------------------------ */
        function render() {
            if (!rows.length) {
                $('#list').html(`<div class="aiset-empty"><div class="big"><i class="fa-solid fa-plug-circle-bolt"></i></div>
                    <h5>No AI provider connected yet</h5>
                    <p class="mb-3">Pick a provider, paste its API key and choose a model. It takes about a minute.</p>
                    <button type="button" class="aiset-btn" id="emptyAdd"><i class="fa-solid fa-plus"></i> Add AI provider</button></div>`);
                return;
            }
            $('#list').html(rows.map((c, i) => {
                const cost = money(c.est_cost);
                return `<div class="aiset-card ${c.is_active ? '' : 'off'}" data-id="${c.id}">
                    <div class="aiset-badge" style="background:${esc(c.provider_color)}">${esc(c.provider_initial)}</div>
                    <div>
                        <div class="aiset-title">${esc(c.name)}
                            <span class="aiset-rank ${i === 0 ? '' : 'backup'}">${i === 0 ? 'Primary' : 'Backup ' + i}</span>
                            <span class="aiset-pill ${c.is_active ? c.status : 'off'}">${c.is_active ? label[c.status] : 'Turned off'}</span>
                        </div>
                        <div class="aiset-sub">
                            <span><i class="fa-solid fa-cube"></i>${esc(c.provider_label)} &middot; ${esc(c.model_label)}</span>
                            ${c.tier ? `<span class="aiset-tier">${esc(c.tier)}</span>` : ''}
                            <span><i class="fa-solid fa-key"></i>${esc(c.key_hint || '')}</span>
                            ${c.notify_email ? `<span><i class="fa-regular fa-envelope"></i>${esc(c.notify_email)}</span>` : ''}
                        </div>
                        <div class="aiset-use">${c.requests ? `<strong>${c.requests.toLocaleString()}</strong> requests &middot; <strong>${tokens(c.input_tokens)}</strong> in / <strong>${tokens(c.output_tokens)}</strong> out tokens${cost ? ` &middot; about <strong>${cost}</strong> so far` : ''}` : 'Not used yet'}
                            ${c.last_used_at ? ` &middot; last used ${esc(c.last_used_at)}` : ''}</div>
                        ${c.status === 'failing' && c.last_error ? `<div class="aiset-err"><i class="fa-solid fa-circle-exclamation" style="margin-top:3px"></i><span>${esc(c.last_error)}</span></div>` : ''}
                    </div>
                    <div class="aiset-actions">
                        <button class="aiset-ghost act-up" title="Move up" aria-label="Move up" ${i === 0 ? 'disabled' : ''}><i class="fa-solid fa-arrow-up"></i></button>
                        <button class="aiset-ghost act-down" title="Move down" aria-label="Move down" ${i === rows.length - 1 ? 'disabled' : ''}><i class="fa-solid fa-arrow-down"></i></button>
                        <button class="aiset-ghost act-test"><i class="fa-solid fa-plug"></i> Test</button>
                        <button class="aiset-ghost act-edit" title="Edit" aria-label="Edit"><i class="fa-solid fa-pen"></i></button>
                        <button class="aiset-ghost danger act-del" title="Delete" aria-label="Delete"><i class="fa-solid fa-trash"></i></button>
                    </div>
                </div>`;
            }).join(''));
        }

        function load() { return $.get(base + '/data', res => { rows = res.data; render(); }); }
        const find = el => rows.find(r => r.id === $(el).closest('.aiset-card').data('id'));
        const fail = x => toastr.error(x.responseJSON?.message || Object.values(x.responseJSON?.errors || {}).flat().join(' ') || 'Something went wrong.');

        /* ------------------------------ drawer ------------------------------ */
        function openDrawer() {
            $('#drawerOverlay').addClass('open'); $('#drawer').addClass('open').attr('aria-hidden', 'false');
            setTimeout(() => $('#tiles .aiset-tile.sel, #tiles .aiset-tile').first().trigger('focus'), 260);
        }
        function closeDrawer() { $('#drawerOverlay').removeClass('open'); $('#drawer').removeClass('open').attr('aria-hidden', 'true'); }
        $('#drawerClose, #drawerCancel, #drawerOverlay').on('click', closeDrawer);
        $(document).on('keydown', e => { if (e.key === 'Escape') closeDrawer(); });

        function renderTiles() {
            $('#tiles').html(PROVIDERS.map(p => `<button type="button" class="aiset-tile ${sel.provider === p.key ? 'sel' : ''}" data-p="${p.key}">
                <span class="aiset-badge" style="background:${esc(p.color)}">${esc(p.initial)}</span>
                <span><b>${esc(p.label)}${p.free_tier ? '<span class="aiset-free">Free tier</span>' : ''}</b><small>${esc(p.tagline)}</small></span></button>`).join(''));
        }

        function pickProvider(key) {
            const p = byKey[key];
            sel.provider = key;
            if (!sel.editing) { sel.model = p.default_model || ''; sel.custom = !!p.custom_only; }
            renderTiles();
            $('#stepKey, #stepModel, #stepDetails').removeClass('locked');
            $('#getKeyText').html(`<b>1.</b> Open ${esc(p.vendor)} and create a key. <b>2.</b> Copy it and paste it below. <span style="display:block;margin-top:2px;">${esc(p.key_hint)}</span>`);
            $('#getKeyBtn').attr('href', p.key_url).html(`<i class="fa-solid fa-arrow-up-right-from-square"></i> Get ${esc(p.label)} API key`).show();
            renderModels();
            if (!$('#cfgName').val() || $('#cfgName').data('auto')) $('#cfgName').val(p.label + ' key').data('auto', true);
        }

        function renderModels() {
            const p = byKey[sel.provider];
            $('#models').html(p.models.map(m => {
                const per = m.per_1000 === null || m.per_1000 === undefined ? null : money(m.per_1000);
                return `<button type="button" class="aiset-model ${!sel.custom && sel.model === m.id ? 'sel' : ''}" data-m="${esc(m.id)}">
                    <span class="aiset-radio"></span>
                    <span><span class="nm">${esc(m.label)} <span class="aiset-tier">${esc(m.tier)}</span></span><span class="ds">${esc(m.note)}</span></span>
                    <span class="pr"><b>${per ? '~' + per + ' / 1,000 questions' : 'Price on provider site'}</b>${priceLine(m)} per 1M tokens</span></button>`;
            }).join('') + `<button type="button" class="aiset-model ${sel.custom ? 'sel' : ''}" data-m="__custom">
                <span class="aiset-radio"></span><span><span class="nm">Other model</span><span class="ds">Type any model id this provider supports.</span></span><span class="pr"></span></button>`);
            $('#customModelWrap').toggle(sel.custom);
            $('#priceNote').html(`Indicative prices in US dollars per million tokens (${esc(PRICED_AT)}); a typical question uses about ${SAMPLE_IN.toLocaleString()} input and ${SAMPLE_OUT.toLocaleString()} output tokens. Prices change, so check <a href="${esc(p.pricing_url)}" target="_blank" rel="noopener noreferrer">${esc(p.label)} pricing</a>. Cheaper models cost less per question but are less capable.`);
        }

        $(document).on('click', '.aiset-tile', function () { pickProvider($(this).data('p')); });
        $(document).on('click', '.aiset-model', function () {
            const m = $(this).data('m');
            if (m === '__custom') { sel.custom = true; $('#cfgModel').trigger('focus'); } else { sel.custom = false; sel.model = m; }
            renderModels();
            if (sel.custom) $('#cfgModel').trigger('focus');
        });
        $('#eyeBtn').on('click', function () {
            const k = $('#cfgKey'), show = k.attr('type') === 'password';
            k.attr('type', show ? 'text' : 'password'); $(this).html(`<i class="fa-regular fa-eye${show ? '-slash' : ''}"></i>`);
        });
        $('#cfgName').on('input', function () { $(this).data('auto', false); });

        function resetDrawer() {
            sel = { provider: null, model: '', custom: false, editing: null };
            $('#cfgForm')[0].reset(); $('#cfgId').val(''); $('#cfgActive').prop('checked', true); $('#cfgName').val('').data('auto', true);
            $('#cfgKey').attr('type', 'password').prop('required', true); $('#eyeBtn').html('<i class="fa-regular fa-eye"></i>');
            $('#stepKey, #stepModel, #stepDetails').addClass('locked');
            $('#getKeyBtn').hide(); $('#getKeyText').text('Choose a provider first.'); $('#models').empty(); $('#priceNote').empty(); $('#customModelWrap').hide();
            $('#cfgKeyHint').text('Stored encrypted and never shown again.');
            $('#cfgSaveText').text('Save & test'); renderTiles();
        }

        $(document).on('click', '#addBtn, #emptyAdd', function () { resetDrawer(); $('#drawerTitle').text('Add AI provider'); openDrawer(); });

        $(document).on('click', '.act-edit', function () {
            const c = find(this);
            resetDrawer();
            sel.editing = c.id;
            $('#drawerTitle').text('Edit ' + c.name);
            $('#cfgId').val(c.id); $('#cfgName').val(c.name).data('auto', false); $('#cfgEmail').val(c.notify_email || ''); $('#cfgActive').prop('checked', c.is_active);
            const p = byKey[c.provider];
            sel.model = c.model || p.default_model || '';
            sel.custom = !!c.model && !p.models.some(m => m.id === c.model);
            pickProvider(c.provider);
            if (sel.custom) $('#cfgModel').val(c.model);
            $('#cfgKey').prop('required', false);
            $('#cfgKeyHint').text('A key ending ' + (c.key_hint || '') + ' is saved. Leave blank to keep it, or paste a new one to replace it.');
            $('#cfgSaveText').text('Save changes');
            openDrawer();
        });

        $('#cfgForm').on('submit', function (e) {
            e.preventDefault();
            if (!sel.provider) { toastr.error('Choose a provider first.'); return; }
            const id = $('#cfgId').val();
            const model = sel.custom ? $('#cfgModel').val().trim() : sel.model;
            const data = { name: $('#cfgName').val(), provider: sel.provider, api_key: $('#cfgKey').val(), notify_email: $('#cfgEmail').val(), model: model, is_active: $('#cfgActive').is(':checked') ? 1 : 0 };
            $('#cfgSave').prop('disabled', true);
            $.ajax({ url: id ? `${base}/${id}` : base, method: id ? 'PUT' : 'POST', data })
                .done(res => {
                    closeDrawer();
                    const newId = id || res.data.id;
                    toastr.success(res.message);
                    load().then(() => {
                        if (!id) { // a brand-new key is checked straight away
                            $.post(`${base}/${newId}/test`).done(r => r.status ? toastr.success(r.message) : toastr.error(r.message)).fail(x => toastr.error(x.responseJSON?.message || 'Test failed.')).always(load);
                        }
                    });
                })
                .fail(fail).always(() => $('#cfgSave').prop('disabled', false));
        });

        /* ------------------------------ row actions ------------------------------ */
        $(document).on('click', '.act-test', function () {
            const c = find(this);
            $(this).prop('disabled', true).html('<i class="fa-solid fa-spinner fa-spin"></i> Testing');
            $.post(`${base}/${c.id}/test`)
                .done(res => res.status ? toastr.success(res.message) : toastr.error(res.message))
                .fail(x => toastr.error(x.responseJSON?.message || 'Test failed.'))
                .always(() => load());
        });
        $(document).on('click', '.act-up, .act-down', function () {
            const c = find(this);
            $.post(`${base}/${c.id}/move`, { direction: $(this).hasClass('act-up') ? 'up' : 'down' }).done(load).fail(fail);
        });
        $(document).on('click', '.act-del', function () {
            const c = find(this);
            if (!confirm(`Remove "${c.name}"? This cannot be undone.`)) return;
            $.ajax({ url: `${base}/${c.id}`, method: 'DELETE' }).done(res => { toastr.success(res.message); load(); }).fail(fail);
        });

        renderTiles();
        load();
    </script>
</body>
</html>
