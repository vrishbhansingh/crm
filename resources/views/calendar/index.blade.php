<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Calendar | CRM</title>

    <link rel="stylesheet" href="{{ asset('vendors/css/vendor.bundle.base.css') }}">
    <link rel="stylesheet" href="{{ asset('css/vertical-layout-light/style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

    <style>
        :root { --primary: #2563eb; --text-dark: #111827; --text-muted: #6b7280; }

        .crm-page-header {
            background: #fff; padding: 18px 22px; border-radius: 14px;
            box-shadow: 0 6px 18px rgba(0, 0, 0, 0.06); border-left: 4px solid var(--primary);
            display: flex; align-items: center; justify-content: space-between; gap: 14px; margin-bottom: 20px; flex-wrap: wrap;
        }
        .crm-header-left { display: flex; align-items: center; gap: 14px; }
        .crm-header-icon {
            width: 42px; height: 42px; border-radius: 10px;
            background: linear-gradient(135deg, #0d6efd, #00c6ff); color: #fff;
            display: flex; align-items: center; justify-content: center; font-size: 18px;
        }
        .crm-page-header h4 { font-weight: 700; font-size: 18px; color: var(--text-dark); margin: 0; }
        .crm-subtitle { color: var(--text-muted); font-size: 13px; }

        .legend { display: flex; gap: 16px; flex-wrap: wrap; }
        .legend-item { display: flex; align-items: center; gap: 6px; font-size: 12.5px; color: var(--text-muted); }
        .legend-dot { width: 10px; height: 10px; border-radius: 50%; display: inline-block; }

        .crm-card {
            background: #fff; border-radius: 14px; box-shadow: 0 8px 22px rgba(0, 0, 0, 0.05);
            border: 1px solid #e5e7eb; padding: 20px;
        }

        #calendar { max-width: 100%; }
        .fc { font-family: inherit; }
        .fc .fc-toolbar-title { font-size: 18px; font-weight: 700; color: var(--text-dark); }
        .fc .fc-button-primary { background: var(--primary); border-color: var(--primary); }
        .fc .fc-button-primary:hover { background: #1d4ed8; border-color: #1d4ed8; }
        .fc .fc-button-primary:not(:disabled).fc-button-active { background: #1d4ed8; border-color: #1d4ed8; }
        .fc-event { cursor: pointer; border: none; font-size: 12px; padding: 1px 4px; }
        .fc-daygrid-event-dot { display: none; }

        /* The vendor table theme forces white link text app-wide (for its
           own dark table headers) — that rule's selector is specific enough
           to bleed into FullCalendar's list view, so its event titles need
           an explicit override back to a normal readable color. */
        .fc-list-event-title a, .fc-list-event-time { color: var(--text-dark) !important; }
        .fc-list-day-cushion { background: #f8fafc !important; }
    </style>
</head>

<body>

    <div class="container-scroller">
        @include('include.header')

        <div class="container-fluid page-body-wrapper">
            @include('include.sidebar')

            <div class="content-wrapper">

                <div class="crm-page-header">
                    <div class="crm-header-left">
                        <div class="crm-header-icon"><i class="fa fa-calendar"></i></div>
                        <div>
                            <h4>Calendar</h4>
                            <small class="crm-subtitle">Task reminders, lead follow-ups, and expected deal close dates in one place.</small>
                        </div>
                    </div>
                    <div class="legend">
                        <span class="legend-item"><span class="legend-dot" style="background:#dc2626"></span> Urgent task</span>
                        <span class="legend-item"><span class="legend-dot" style="background:#2563eb"></span> Task reminder</span>
                        <span class="legend-item"><span class="legend-dot" style="background:#7c3aed"></span> Lead follow-up</span>
                        <span class="legend-item"><span class="legend-dot" style="background:#16a34a"></span> Deal close date</span>
                    </div>
                </div>

                <div class="crm-card">
                    <div id="calendar"></div>
                </div>

                @include('include.footer')
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="{{ asset('vendors/js/vendor.bundle.base.js') }}"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/fullcalendar/6.1.15/index.global.min.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const calendarEl = document.getElementById('calendar');
            const calendar = new FullCalendar.Calendar(calendarEl, {
                initialView: 'dayGridMonth',
                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'dayGridMonth,timeGridWeek,listMonth',
                },
                height: 'auto',
                events: function(info, successCallback, failureCallback) {
                    $.get("{{ route('calendar.events') }}", {
                        start: info.startStr,
                        end: info.endStr,
                    }).done(successCallback).fail(failureCallback);
                },
                eventClick: function(info) {
                    if (info.event.url) {
                        info.jsEvent.preventDefault();
                        window.location.href = info.event.url;
                    }
                },
            });
            calendar.render();
        });
    </script>

</body>

</html>
