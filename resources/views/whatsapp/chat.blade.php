<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>WhatsApp Chat | CRM Admin</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" />

    <style>
        :root { --primary: #25d366; --primary-dark: #128c7e; --border: #e5e7eb; --text-dark: #111827; --text-muted: #6b7280; }

        .chat-shell { display: flex; height: calc(100vh - 150px); background: #fff; border-radius: 14px; overflow: hidden; box-shadow: 0 8px 22px rgba(0,0,0,.06); border: 1px solid var(--border); }
        .chat-list { width: 320px; border-right: 1px solid var(--border); display: flex; flex-direction: column; }
        .chat-list-header { padding: 12px 14px; border-bottom: 1px solid var(--border); }
        .chat-list-items { flex: 1; overflow-y: auto; }
        .chat-item { padding: 12px 14px; border-bottom: 1px solid #f3f4f6; cursor: pointer; display: flex; justify-content: space-between; gap: 8px; }
        .chat-item:hover, .chat-item.active { background: #f0fdf4; }
        .chat-item .ci-name { font-weight: 600; font-size: 13.5px; color: var(--text-dark); }
        .chat-item .ci-preview { font-size: 12px; color: var(--text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }
        .chat-item .ci-time { font-size: 10.5px; color: #9ca3af; }
        .unread-badge { background: var(--primary); color: #fff; border-radius: 999px; font-size: 10px; padding: 1px 6px; }

        .chat-thread { flex: 1; display: flex; flex-direction: column; background: #efeae2; }
        .chat-thread-header { padding: 12px 18px; background: #fff; border-bottom: 1px solid var(--border); display: flex; justify-content: space-between; align-items: center; }
        .chat-messages { flex: 1; overflow-y: auto; padding: 18px; display: flex; flex-direction: column; gap: 8px; }
        .msg-bubble { max-width: 60%; padding: 8px 12px; border-radius: 10px; font-size: 13.5px; line-height: 1.4; position: relative; }
        .msg-bubble.out { align-self: flex-end; background: #dcf8c6; }
        .msg-bubble.in { align-self: flex-start; background: #fff; }
        .msg-bubble .msg-meta { font-size: 10px; color: #9ca3af; margin-top: 3px; text-align: right; }
        .msg-bubble.failed { border: 1px solid #fca5a5; }
        .msg-bubble .msg-error { color: #b91c1c; font-size: 11px; margin-top: 3px; }

        .chat-compose { padding: 12px 16px; background: #fff; border-top: 1px solid var(--border); }
        .window-banner { background: #fef3c7; color: #92400e; padding: 6px 16px; font-size: 12px; }
        .chat-empty { display: flex; align-items: center; justify-content: center; height: 100%; color: var(--text-muted); flex-direction: column; }
    </style>
</head>

<body>
    <div class="container-scroller">
        @include('include.header')
        <div class="container-fluid page-body-wrapper">
            @include('include.sidebar')
            <div class="content-wrapper">

                <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap" style="gap:10px;">
                    <h4 style="font-weight:700;"><i class="fa fa-whatsapp text-success"></i> WhatsApp Chat</h4>
                    <div class="d-flex gap-2">
                        <select id="accountFilter" class="form-control form-control-sm" style="width:auto;">
                            <option value="">All accounts</option>
                            @foreach($accounts as $acc)
                                <option value="{{ $acc->id }}">{{ $acc->name }}</option>
                            @endforeach
                        </select>
                        <button class="btn btn-sm btn-success" data-toggle="modal" data-target="#startChatModal" @if($accounts->isEmpty()) disabled title="Connect a WhatsApp account first" @endif><i class="fa fa-plus"></i> New Chat</button>
                    </div>
                </div>

                @if($accounts->isEmpty())
                    <div class="alert alert-warning">No active WhatsApp account yet. <a href="{{ route('whatsapp.settings.index') }}">Connect one first</a>.</div>
                @endif

                <div class="chat-shell">
                    <div class="chat-list">
                        <div class="chat-list-header"><input type="text" id="chatSearch" class="form-control form-control-sm" placeholder="Search name or number..."></div>
                        <div class="chat-list-items" id="chatListItems"></div>
                    </div>
                    <div class="chat-thread">
                        <div id="threadEmpty" class="chat-empty"><i class="fa fa-comments-o" style="font-size:40px;"></i><p class="mt-2">Select a conversation to view messages</p></div>
                        <div id="threadBody" style="display:none; flex:1; display:flex; flex-direction:column;">
                            <div class="chat-thread-header">
                                <div>
                                    <div style="font-weight:700;" id="threadName">-</div>
                                    <small class="text-muted" id="threadPhone">-</small>
                                </div>
                            </div>
                            <div id="windowBanner" class="window-banner" style="display:none;">
                                <i class="fa fa-clock-o"></i> More than 24 hours since this contact last messaged — only an approved template message can be sent now.
                            </div>
                            <div class="chat-messages" id="chatMessages"></div>
                            <div class="chat-compose">
                                <form id="sendForm" class="d-flex gap-2">
                                    <input type="text" id="composeText" class="form-control" placeholder="Type a message...">
                                    <button class="btn btn-success" type="submit"><i class="fa fa-paper-plane"></i></button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <div class="modal fade" id="startChatModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="startChatForm">
                    <div class="modal-header"><h5 class="modal-title">Start a New Chat</h5><button type="button" class="close" data-dismiss="modal">&times;</button></div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Send From</label>
                            <select name="account_id" class="form-control" required>
                                @foreach($accounts as $acc)<option value="{{ $acc->id }}">{{ $acc->name }}</option>@endforeach
                            </select>
                        </div>
                        <div class="form-group"><label>Phone Number</label><input type="text" name="phone" class="form-control" placeholder="+91 98765 43210" required></div>
                        <div class="form-group"><label>Name <small class="text-muted">(optional)</small></label><input type="text" name="name" class="form-control"></div>
                    </div>
                    <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Cancel</button><button class="btn btn-success" type="submit">Start Chat</button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });

        var activeConversationId = null;

        function timeAgo(dateStr) {
            if (!dateStr) return '';
            var d = new Date(dateStr);
            return d.toLocaleDateString() + ' ' + d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        }

        function loadConversations() {
            $.get('/whatsapp/chat/conversations', {
                account_id: $('#accountFilter').val(),
                search: $('#chatSearch').val()
            }, function (res) {
                var html = '';
                if (!res.data.length) {
                    html = '<div class="text-center text-muted p-3">No conversations yet.</div>';
                }
                res.data.forEach(function (c) {
                    html += '<div class="chat-item' + (c.id === activeConversationId ? ' active' : '') + '" data-id="' + c.id + '">'
                        + '<div><div class="ci-name">' + $('<div>').text(c.display_name).html() + '</div>'
                        + '<div class="ci-preview">' + $('<div>').text(c.last_message_preview || '').html() + '</div></div>'
                        + '<div class="text-right"><div class="ci-time">' + timeAgo(c.last_message_at) + '</div>'
                        + (c.unread_count ? '<span class="unread-badge">' + c.unread_count + '</span>' : '') + '</div>'
                        + '</div>';
                });
                $('#chatListItems').html(html);
            });
        }

        function openConversation(id) {
            activeConversationId = id;
            $('#threadEmpty').hide();
            $('#threadBody').show().css('display', 'flex');

            $.get('/whatsapp/chat/conversations/' + id + '/messages', function (res) {
                var conv = res.data.conversation;
                $('#threadName').text(conv.display_name);
                $('#threadPhone').text(conv.wa_phone);
                $('#windowBanner').toggle(conv.channel_type === 'meta_cloud' && !conv.within_window);

                var html = '';
                res.data.messages.forEach(function (m) {
                    html += '<div class="msg-bubble ' + m.direction + (m.status === 'failed' ? ' failed' : '') + '">'
                        + $('<div>').text(m.body || ('[' + m.type + ']')).html()
                        + '<div class="msg-meta">' + timeAgo(m.created_at) + (m.direction === 'out' ? ' &middot; ' + m.status : '') + '</div>'
                        + (m.status === 'failed' && m.error_message ? '<div class="msg-error">' + $('<div>').text(m.error_message).html() + '</div>' : '')
                        + '</div>';
                });
                $('#chatMessages').html(html);
                $('#chatMessages').scrollTop($('#chatMessages')[0].scrollHeight);
                loadConversations();
            });
        }

        $('#accountFilter, #chatSearch').on('change keyup', function () { loadConversations(); });

        $(document).on('click', '.chat-item', function () { openConversation($(this).data('id')); });

        $('#sendForm').on('submit', function (e) {
            e.preventDefault();
            var text = $('#composeText').val().trim();
            if (!text || !activeConversationId) return;

            $.post('/whatsapp/chat/conversations/' + activeConversationId + '/send', { type: 'text', body: text }, function (res) {
                if (res.status) {
                    $('#composeText').val('');
                    openConversation(activeConversationId);
                } else {
                    toastr.error(res.message);
                }
            }).fail(function (xhr) {
                toastr.error(xhr.responseJSON && xhr.responseJSON.message ? xhr.responseJSON.message : 'Failed to send.');
            });
        });

        $('#startChatForm').on('submit', function (e) {
            e.preventDefault();
            $.post('/whatsapp/chat/start', $(this).serialize(), function (res) {
                $('#startChatModal').modal('hide');
                loadConversations();
                openConversation(res.data.id);
            });
        });

        loadConversations();
        setInterval(loadConversations, 15000);
    </script>
</body>
</html>
