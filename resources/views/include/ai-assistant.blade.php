@php
    $aiUser = Auth::guard('web')->user();
    $aiOn = $aiUser && app(\App\Services\Ai\AssistantService::class)->available();
    $aiElevated = $aiOn && $aiUser->hasElevatedAccess();
    // Computed here, not inline in @json(...): Blade splits @json's argument on commas and breaks array literals.
    $aiSuggestions = $aiElevated
        ? ['How is my team performing?', 'Which leads are overdue for follow-up?', 'Show the deal pipeline', 'Any unassigned leads?', 'Which deals are stalled?']
        : ['What are my follow-ups today?', 'Show my open deals', 'Which of my tasks are overdue?', 'How many new leads do I have?'];
@endphp
@if ($aiOn)
<style>
    .ai-fab { position: fixed; right: 22px; bottom: 22px; z-index: 1040; width: 52px; height: 52px; border-radius: 50%; border: 0; cursor: pointer;
        background: var(--crm-accent, #1f6f63); color: #fff; font-size: 20px; box-shadow: 0 6px 18px rgba(0,0,0,.22); }
    .ai-fab:hover { filter: brightness(1.08); }
    .ai-panel { position: fixed; right: 22px; bottom: 84px; z-index: 1040; width: 380px; max-width: calc(100vw - 24px); height: 540px; max-height: calc(100vh - 110px);
        display: none; flex-direction: column; background: var(--crm-surface, #fff); color: var(--crm-ink, #171a17);
        border: 1px solid var(--crm-border, #e5e7eb); border-radius: 14px; box-shadow: 0 16px 40px rgba(0,0,0,.22); overflow: hidden; font-size: 13px; }
    .ai-panel.open { display: flex; }
    .ai-head { padding: 12px 14px; display: flex; align-items: center; justify-content: space-between; background: var(--crm-accent, #1f6f63); color: #fff; }
    .ai-head strong { font-size: 14px; }
    .ai-head small { display: block; opacity: .85; font-size: 11px; }
    .ai-head button { background: transparent; border: 0; color: #fff; cursor: pointer; font-size: 14px; opacity: .85; margin-left: 8px; }
    .ai-body { flex: 1; overflow-y: auto; padding: 12px; display: flex; flex-direction: column; gap: 8px; background: var(--crm-surface-2, #f3f4f6); }
    .ai-msg { max-width: 88%; padding: 8px 11px; border-radius: 12px; line-height: 1.45; word-wrap: break-word; }
    .ai-msg.user { align-self: flex-end; background: var(--crm-accent, #1f6f63); color: #fff; border-bottom-right-radius: 4px; }
    .ai-msg.bot { align-self: flex-start; background: var(--crm-surface, #fff); border: 1px solid var(--crm-border, #e5e7eb); border-bottom-left-radius: 4px; }
    .ai-msg ul { margin: 4px 0 0 16px; padding: 0; }
    .ai-chips { display: flex; flex-wrap: wrap; gap: 6px; }
    .ai-chip { border: 1px solid var(--crm-border, #e5e7eb); background: var(--crm-surface, #fff); color: inherit; border-radius: 999px; padding: 5px 10px; font-size: 12px; cursor: pointer; }
    .ai-chip:hover { border-color: var(--crm-accent, #1f6f63); }
    .ai-form { display: flex; gap: 8px; padding: 10px; border-top: 1px solid var(--crm-border, #e5e7eb); background: var(--crm-surface, #fff); }
    .ai-form input { flex: 1; border: 1px solid var(--crm-border, #e5e7eb); border-radius: 8px; padding: 8px 10px; font-size: 13px; background: var(--crm-surface-2, #f3f4f6); color: inherit; }
    .ai-form input:focus { outline: none; border-color: var(--crm-accent, #1f6f63); }
    .ai-form button { border: 0; border-radius: 8px; padding: 0 14px; background: var(--crm-accent, #1f6f63); color: #fff; cursor: pointer; }
    .ai-form button:disabled { opacity: .55; cursor: default; }
    .ai-typing { opacity: .7; font-style: italic; }
    .ai-card { align-self: stretch; border: 1px solid var(--crm-accent, #1f6f63); border-radius: 10px; padding: 10px 12px; background: var(--crm-surface, #fff); }
    .ai-card-text { margin-bottom: 8px; line-height: 1.4; }
    .ai-card-actions { display: flex; gap: 8px; }
    .ai-card-actions button { border-radius: 8px; padding: 5px 14px; font-size: 12px; cursor: pointer; border: 1px solid var(--crm-border, #e5e7eb); background: var(--crm-surface-2, #f3f4f6); color: inherit; }
    .ai-card-actions .ai-card-ok { background: var(--crm-accent, #1f6f63); border-color: var(--crm-accent, #1f6f63); color: #fff; }
    .ai-card-actions button:disabled { opacity: .55; cursor: default; }
    .ai-card.done { border-color: #2e8b57; } .ai-card.dismissed { opacity: .7; border-color: var(--crm-border, #e5e7eb); }
</style>

<button type="button" class="ai-fab" id="aiFab" aria-label="Open CRM assistant" title="CRM Assistant">&#10024;</button>

<div class="ai-panel" id="aiPanel" role="dialog" aria-label="CRM Assistant">
    <div class="ai-head">
        <div><strong>CRM Assistant</strong><small>{{ $aiElevated ? 'Admin view: whole company' : 'Your leads, deals & tasks' }}</small></div>
        <div>
            <button type="button" id="aiReset" title="New conversation" aria-label="New conversation">&#8635;</button>
            <button type="button" id="aiClose" title="Close" aria-label="Close">&#10005;</button>
        </div>
    </div>
    <div class="ai-body" id="aiBody"></div>
    <form class="ai-form" id="aiForm" autocomplete="off">
        <input type="text" id="aiInput" maxlength="{{ config('ai.max_question_length') }}" placeholder="Ask about your CRM…" aria-label="Message">
        <button type="submit" id="aiSend">Send</button>
    </form>
</div>

<script>
(function () {
    const csrf = @json(csrf_token());
    const url = @json(route('assistant.message'));
    const resetUrl = @json(route('assistant.reset'));
    const confirmUrl = @json(route('assistant.confirm'));
    const cancelUrl = @json(route('assistant.cancel'));
    const suggestions = @json($aiSuggestions);
    const fab = document.getElementById('aiFab'), panel = document.getElementById('aiPanel'), body = document.getElementById('aiBody');
    const form = document.getElementById('aiForm'), input = document.getElementById('aiInput'), send = document.getElementById('aiSend');

    function esc(s) { return s.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;'); }
    function render(text) {
        const lines = esc(text).split('\n'); let html = '', list = false;
        lines.forEach(function (line) {
            const li = line.match(/^\s*[-*•]\s+(.*)$/);
            if (li) { if (!list) { html += '<ul>'; list = true; } html += '<li>' + li[1] + '</li>'; return; }
            if (list) { html += '</ul>'; list = false; }
            html += line.trim() === '' ? '<br>' : line + '<br>';
        });
        if (list) html += '</ul>';
        return html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>').replace(/(<br>)+$/, '');
    }
    function add(role, text, typing) {
        const el = document.createElement('div');
        el.className = 'ai-msg ' + role + (typing ? ' ai-typing' : '');
        if (role === 'user' || typing) el.textContent = text; else el.innerHTML = render(text);
        body.appendChild(el); body.scrollTop = body.scrollHeight; return el;
    }
    function welcome() {
        body.innerHTML = '';
        add('bot', 'Hi! I can answer questions about your CRM data, and prepare tasks, notes and lead status updates for you to confirm. Try one of these:');
        const chips = document.createElement('div'); chips.className = 'ai-chips';
        suggestions.forEach(function (q) {
            const b = document.createElement('button'); b.type = 'button'; b.className = 'ai-chip'; b.textContent = q;
            b.addEventListener('click', function () { ask(q); }); chips.appendChild(b);
        });
        body.appendChild(chips);
    }
    // A change the assistant has prepared. Nothing happens until Confirm is pressed.
    function addCard(p) {
        const card = document.createElement('div'); card.className = 'ai-card';
        const text = document.createElement('div'); text.className = 'ai-card-text'; text.textContent = p.summary;
        const row = document.createElement('div'); row.className = 'ai-card-actions';
        const ok = document.createElement('button'); ok.type = 'button'; ok.className = 'ai-card-ok'; ok.textContent = 'Confirm';
        const no = document.createElement('button'); no.type = 'button'; no.className = 'ai-card-no'; no.textContent = 'Cancel';
        row.appendChild(ok); row.appendChild(no); card.appendChild(text); card.appendChild(row); body.appendChild(card); body.scrollTop = body.scrollHeight;
        function settle(msg, good) { card.innerHTML = ''; card.classList.add(good ? 'done' : 'dismissed'); card.textContent = msg; }
        function call(endpoint) {
            ok.disabled = true; no.disabled = true;
            return fetch(endpoint, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify({ id: p.id }) }).then(function (r) { return r.json().catch(function () { return {}; }); });
        }
        ok.addEventListener('click', function () { call(confirmUrl).then(function (d) { settle((d.status ? '✓ ' : '✕ ') + (d.message || 'Something went wrong.'), !!d.status); }).catch(function () { settle('Could not reach the server.', false); }); });
        no.addEventListener('click', function () { call(cancelUrl).then(function () { settle('Cancelled. Nothing was changed.', false); }).catch(function () { settle('Cancelled.', false); }); });
    }
    async function ask(q) {
        if (!q.trim()) return;
        add('user', q); input.value = ''; send.disabled = true; input.disabled = true;
        const wait = add('bot', 'Looking that up…', true);
        try {
            const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrf, 'X-Requested-With': 'XMLHttpRequest' }, body: JSON.stringify({ message: q }) });
            const data = await res.json().catch(function () { return {}; });
            wait.remove();
            add('bot', res.ok && data.reply ? data.reply : (data.message || 'Something went wrong. Please try again.'));
            (data.pending || []).forEach(addCard);
        } catch (e) { wait.remove(); add('bot', 'Could not reach the server. Check your connection and try again.'); }
        send.disabled = false; input.disabled = false; input.focus();
    }
    fab.addEventListener('click', function () { panel.classList.toggle('open'); if (panel.classList.contains('open')) input.focus(); });
    document.getElementById('aiClose').addEventListener('click', function () { panel.classList.remove('open'); });
    document.getElementById('aiReset').addEventListener('click', function () {
        fetch(resetUrl, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } }); welcome();
    });
    form.addEventListener('submit', function (e) { e.preventDefault(); ask(input.value); });
    welcome();
})();
</script>
@endif
