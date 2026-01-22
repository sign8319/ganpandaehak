<?php
$sub_menu = '100990';
include_once('./_common.php');

if ($is_admin != 'super')
    alert('최고관리자만 접근 가능합니다.');

$mode = isset($_GET['mode']) ? $_GET['mode'] : '';
$is_app_mode = ($mode == 'app');

if (!$is_app_mode) {
    $g5['title'] = '일정관리';
    include_once('./admin.head.php');
} else {
    // App Mode: Minimal Header
    include_once(G5_PATH . '/head.sub.php');
}
?>

<!-- FullCalendar Libraries -->
<script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.10/index.global.min.js'></script>
<script src='https://cdn.jsdelivr.net/npm/@fullcalendar/google-calendar@6.1.10/index.global.min.js'></script>
<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    /* Global Reset for App Mode */
    <?php if ($is_app_mode): ?>
        body {
            margin: 0;
            padding: 0;
            overflow: hidden;
            background: #fff;
        }

    <?php else: ?>
        /* Full Page Layout Override for Admin Mode */
        #wrapper {
            max-width: 100% !important;
            margin: 0 !important;
            padding: 0 !important;
            background: #fdfdfd;
        }

    <?php endif; ?>

    /* Container Fluid */
    .schedule-container {
        padding:
            <?php echo $is_app_mode ? '0' : '10px 20px'; ?>
        ;
        height:
            <?php echo $is_app_mode ? '100vh' : 'calc(100vh - 65px)'; ?>
        ;
        display: flex;
        flex-direction: column;
        overflow: hidden;
        background: #fff;
    }

    /* Header Styling */
    .schedule-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 10px;
        padding: 5px 10px;
        <?php if ($is_app_mode)
            echo 'border-bottom: 1px solid #eee;'; ?>
    }

    .schedule-title {
        font-size: 16px;
        /* Reduced as requested */
        font-weight: 700;
        color: #333;
        display: flex;
        align-items: center;
        gap: 8px;
    }

    /* Calendar Styling */
    #calendar {
        flex-grow: 1;
        background: #fff;
        <?php if (!$is_app_mode)
            echo 'border: 1px solid #eaeaea; border-radius: 8px; padding: 10px;'; ?>
        font-family: 'Pretendard', -apple-system, BlinkMacSystemFont, system-ui, Roboto, sans-serif;
    }

    /* Toolbar Customization */
    .fc-header-toolbar {
        margin-bottom: 10px !important;
        padding: 0 10px;
    }

    .fc-toolbar-title {
        font-size: 1.1rem !important;
        font-weight: 800 !important;
        color: #1f2937;
    }

    .fc-button {
        height: 32px !important;
        /* Standardized height */
        padding: 0 12px !important;
        font-size: 0.8rem !important;
        /* Slightly smaller for business look */
        font-weight: 600 !important;
        border-radius: 6px !important;
        box-shadow: none !important;
    }

    .fc-button-primary {
        background-color: #fff !important;
        color: #4b5563 !important;
        border: 1px solid #d1d5db !important;
    }

    .fc-button-primary:hover {
        background-color: #f9fafb !important;
        color: #111827 !important;
        border-color: #9ca3af !important;
    }

    .fc-button-active {
        background-color: #374151 !important;
        color: #fff !important;
        border-color: #374151 !important;
    }

    .fc-today-button {
        background-color: #f3f4f6 !important;
        color: #374151 !important;
        border: 1px solid #e5e7eb !important;
        opacity: 1 !important;
    }

    /* Table & Cells */
    .fc-col-header-cell {
        padding: 6px 0 !important;
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0 !important;
    }

    .fc-col-header-cell-cushion {
        font-size: 0.85rem;
        font-weight: 700;
        /* Bold headers */
        color: #475569;
    }

    /* Date Numbers */
    .fc-daygrid-day-number {
        font-size: 14px !important;
        /* Requested 13-14px */
        font-weight: 600 !important;
        color: #333;
        padding: 6px 8px !important;
        /* Requested 6px padding */
    }

    .fc-daygrid-day-frame {
        padding: 0 !important;
        /* Remove inner padding to maximize space */
    }

    /* Cell content padding handled via custom rendering or inherit */

    .fc-scrollgrid {
        border-color: #f1f5f9 !important;
    }

    td,
    th {
        border-color: #f1f5f9 !important;
    }

    /* Today Highlight */
    .fc-day-today {
        background-color: #fff7ed !important;
        /* Very subtle orange */
    }

    /* Weekends */
    .fc-day-sun {
        background-color: #fef2f2 !important;
        /* Light Red */
    }

    .fc-day-sun .fc-daygrid-day-number {
        color: #ef4444 !important;
    }

    .fc-day-sat {
        background-color: #f0f9ff !important;
        /* Light Blue */
    }

    .fc-day-sat .fc-daygrid-day-number {
        color: #3b82f6 !important;
    }

    /* Events - Business Card Style & High Density */
    .fc-event {
        border: none !important;
        border-radius: 3px !important;
        box-shadow: none !important;
        margin-bottom: 1px !important;
        /* Compact margin */
        font-size: 11px !important;
        /* Smaller font for density */
        line-height: 1.2 !important;
        font-weight: 600;
        cursor: pointer;
        background-color: transparent !important;
    }

    .fc-event:hover {
        background-color: rgba(0, 0, 0, 0.03) !important;
    }

    .fc-daygrid-event-dot {
        display: none;
    }

    .fc-event-main {
        padding: 1px 20px 1px 2px;
        /* Add right padding for absolute delete btn */
        border-radius: 2px;
        display: flex;
        align-items: center;
        gap: 4px;
        width: 100%;
        color: #1f2937;
        background-color: #e5e5e5;
        overflow: hidden;
        min-height: 18px;
        position: relative;
        /* For absolute children */
    }

    .event-bar-strip {
        width: 3px;
        height: 100%;
        min-height: 14px;
        border-radius: 1px;
        flex-shrink: 0;
    }

    .event-checkbox {
        width: 12px;
        height: 12px;
        cursor: pointer;
        accent-color: #f97316;
        margin: 0;
        flex-shrink: 0;
    }

    .event-title-text {
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        flex: 1;
    }

    .event-delete-btn {
        position: absolute;
        right: 2px;
        top: 50%;
        transform: translateY(-50%);
        width: 16px;
        height: 16px;
        display: flex;
        /* Flex handles centering and allows transform */
        align-items: center;
        justify-content: center;
        font-size: 11px;
        color: #9ca3af;
        border-radius: 50%;
        /* Circle touch target */
        opacity: 0;
        transition: all 0.2s ease;
        z-index: 50;
        /* High z-index */
        cursor: pointer;
        background-color: rgba(255, 255, 255, 0.5);
        /* Slight BG for visibility */
    }

    .fc-event:hover .event-delete-btn {
        opacity: 1;
    }

    .event-delete-btn:hover {
        background-color: #ef4444;
        /* Red BG on hover */
        color: white;
        transform: translateY(-50%) scale(1.2);
        /* Zoom with vertical center */
        box-shadow: 0 1px 2px rgba(0, 0, 0, 0.2);
    }

    /* Done Task Style */
    .done-task .fc-event-main {
        opacity: 0.6;
        text-decoration: line-through;
        background-color: #f3f4f6 !important;
        color: #9ca3af !important;
    }

    .done-task .event-bar-strip {
        background-color: #9ca3af !important;
    }

    /* Popover/Quick Add - Memo Pad Style */
    .quick-add-popover {
        position: absolute;
        z-index: 50;
        background: #fefce8;
        border: 1px solid #d4d4d8;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -1px rgba(0, 0, 0, 0.06);
        width: 300px;
        padding: 0;
        display: none;
        border-radius: 2px;
    }

    .memo-header {
        background: #fef9c3;
        padding: 8px 12px;
        border-bottom: 1px solid #e4e4e7;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .memo-body {
        padding: 8px;
        max-height: 300px;
        overflow-y: auto;
    }

    .memo-row {
        display: flex;
        align-items: center;
        margin-bottom: 4px;
        background: white;
        border: 1px solid #e4e4e7;
        padding: 1px;
    }

    .memo-input {
        flex-grow: 1;
        border: none;
        background: transparent;
        padding: 6px 8px;
        font-size: 13px;
        font-weight: 600;
        outline: none;
        min-width: 0;
    }

    .memo-actions {
        display: flex;
        padding-right: 6px;
        gap: 6px;
        align-items: center;
    }

    .memo-icon {
        color: #9ca3af;
        cursor: pointer;
        font-size: 14px;
        transition: color 0.15s;
        padding: 4px;
    }

    .memo-icon:hover {
        color: #4b5563;
    }

    .memo-icon.active {
        color: #f97316;
    }

    /* Active Alarm */
    .memo-icon.save-btn:hover {
        color: #16a34a;
    }

    /* Modal Override */
    dialog::backdrop {
        background: rgba(0, 0, 0, 0.3);
        backdrop-filter: blur(1px);
    }
</style>

<div class="schedule-container">

    <div class="schedule-header">
        <div class="schedule-title">
            <i class="fas fa-calendar-check text-orange-600"></i> 일정관리
            <span class="text-xs font-normal text-gray-400 ml-2 hidden sm:inline">날짜를 클릭하여 메모하듯 일정을 입력하세요 (Enter로 연속
                저장)</span>
        </div>
        <div class="flex gap-2">
            <?php if ($is_app_mode): ?>
                <button onclick="window.close()"
                    class="text-xs bg-gray-100 hover:bg-gray-200 border border-gray-300 text-gray-600 px-3 py-1.5 rounded transition flex items-center gap-1">
                    <i class="fas fa-times"></i> 닫기
                </button>
            <?php endif; ?>
        </div>
    </div>

    <div id='calendar'></div>

</div>

<!-- Quick Add Popover (Memo Pad) -->
<div id="quickAddPopover" class="quick-add-popover">
    <div class="memo-header">
        <span class="text-xs font-bold text-gray-600" id="qa_date_display">YYYY-MM-DD</span>
        <button type="button" onclick="closeQuickAdd()" class="text-gray-400 hover:text-gray-700">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="memo-body" id="memo_rows_container">
        <!-- Javascript will populate rows here -->
        <!-- Template row structure:
        <div class="memo-row">
            <input type="text" class="memo-input" placeholder="일정 입력...">
            <div class="memo-status"></div>
        </div> 
        -->
    </div>
    <!-- Hidden fields for context -->
    <input type="hidden" id="qa_current_date" value="">
</div>

<!-- Detailed Modal -->
<dialog id="detailModal" class="p-0 rounded-xl shadow-2xl border-0 w-full max-w-md backdrop:bg-gray-900/50">
    <div class="bg-white">
        <!-- Header -->
        <div class="px-5 py-4 border-b border-gray-100 flex justify-between items-center bg-gray-50">
            <h3 class="text-lg font-bold text-gray-800" id="dm_modal_title">일정 상세</h3>
            <div class="flex items-center gap-3">
                <button type="button" onclick="deleteEvent()"
                    class="text-red-500 hover:text-red-700 text-sm font-bold transition mr-2" title="삭제">
                    <i class="fas fa-trash-alt"></i>
                </button>
                <button onclick="closeDetailModal()" class="text-gray-400 hover:text-gray-600 transition">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
        </div>

        <form id="detailForm" onsubmit="return submitDetail(event)" class="p-5 space-y-4">
            <input type="hidden" id="dm_id" name="id">

            <!-- Title & Status -->
            <div class="flex items-start gap-3 bg-gray-50 p-3 rounded-lg border border-gray-100">
                <div class="pt-1">
                    <input type="checkbox" id="dm_is_done" name="is_done" value="1"
                        class="w-5 h-5 text-orange-600 border-gray-300 rounded focus:ring-orange-500 cursor-pointer"
                        onchange="toggleStrike(this)">
                </div>
                <div class="flex-1">
                    <input type="text" id="dm_title" name="title"
                        class="w-full text-base font-bold bg-transparent border-0 border-b border-dashed border-gray-300 focus:border-orange-500 focus:ring-0 px-0 py-1 transition placeholder-gray-400"
                        placeholder="일정 제목" required>
                    <label for="dm_is_done" class="text-xs text-gray-400 mt-1 block cursor-pointer select-none">체크 시 완료
                        처리 (취소선)</label>
                </div>
            </div>

            <!-- Date Time -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">시작</label>
                    <input type="datetime-local" id="dm_start" name="start_datetime"
                        class="w-full text-sm border-gray-200 rounded-lg focus:ring-orange-500 focus:border-orange-500 bg-white"
                        required>
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">종료</label>
                    <input type="datetime-local" id="dm_end" name="end_datetime"
                        class="w-full text-sm border-gray-200 rounded-lg focus:ring-orange-500 focus:border-orange-500 bg-white"
                        required>
                </div>
            </div>

            <!-- Options: Color & Alarm -->
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">색상 구분</label>
                    <div class="flex gap-1.5" id="color_picker">
                        <!-- JS generated colors -->
                    </div>
                    <input type="hidden" id="dm_color" name="color" value="#3788d8">
                </div>
                <div>
                    <label class="block text-xs font-bold text-gray-500 mb-1">알림</label>
                    <select id="dm_alarm" name="alarm_minutes"
                        class="w-full text-sm border-gray-200 rounded-lg focus:ring-orange-500 focus:border-orange-500">
                        <option value="0">없음</option>
                        <option value="10">10분 전</option>
                        <option value="30">30분 전</option>
                        <option value="60">1시간 전</option>
                        <option value="1440">1일 전</option>
                    </select>
                </div>
            </div>

            <!-- Memo (Collapsible) -->
            <div>
                <button type="button" onclick="toggleMemo()"
                    class="flex items-center gap-2 text-sm font-bold text-gray-600 hover:text-gray-900 transition w-full py-1">
                    <i class="fas fa-sticky-note text-yellow-500"></i> 메모 <span id="memo_toggle_text"
                        class="text-xs font-normal text-gray-400 ml-auto">펼치기</span>
                </button>
                <div id="memo_container" class="mt-2" style="display:none;">
                    <textarea id="dm_memo" name="memo" rows="4"
                        class="w-full text-sm border-gray-200 rounded-lg focus:ring-orange-500 focus:border-orange-500 bg-yellow-50/50 resize-none p-3"
                        placeholder="상세 내용을 입력하세요..."></textarea>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="pt-2 flex justify-end gap-2">
                <button type="button" onclick="closeDetailModal()"
                    class="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg text-sm font-bold hover:bg-gray-50 transition shadow-sm">취소</button>
                <button type="submit"
                    class="px-6 py-2 bg-gray-900 text-white rounded-lg text-sm font-bold shadow-md hover:bg-black transition">저장</button>
            </div>
        </form>
    </div>
</dialog>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        var calendarEl = document.getElementById('calendar');
        var quickAddPopover = document.getElementById('quickAddPopover');

        // --- Multi-line Memo Inputs Logic ---

        // Initial setup for Memo Inputs
        function initMemoInputs() {
            const container = document.getElementById('memo_rows_container');
            container.innerHTML = ''; // Clear existing rows
            // Create 4 initial rows
            for (let i = 0; i < 4; i++) {
                addMemoRow(container, i === 0);
            }
        }

        function addMemoRow(container, focus = false) {
            const row = document.createElement('div');
            row.className = 'memo-row';
            row.dataset.alarm = 0; // Default alarm off

            // Input
            const input = document.createElement('input');
            input.type = 'text';
            input.className = 'memo-input';
            input.placeholder = '일정 입력';
            input.autocomplete = 'off';

            // Actions Container
            const actions = document.createElement('div');
            actions.className = 'memo-actions';

            // 1. Alarm Icon
            const alarmBtn = document.createElement('i');
            alarmBtn.className = 'fas fa-clock memo-icon';
            alarmBtn.title = '알림 10분 전 설정';
            alarmBtn.onclick = function () {
                const current = parseInt(row.dataset.alarm);
                if (current === 0) {
                    row.dataset.alarm = 10;
                    this.classList.add('active');
                } else {
                    row.dataset.alarm = 0;
                    this.classList.remove('active');
                }
            };

            // 2. Save/Add Icon
            const saveBtn = document.createElement('i');
            saveBtn.className = 'fas fa-plus-square memo-icon save-btn';
            saveBtn.title = '저장';
            saveBtn.onclick = function () {
                if (input.value.trim() !== '') {
                    saveMemoRow(input, saveBtn, container);
                } else {
                    input.focus();
                }
            };

            // Key Handler
            input.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    if (this.value.trim() !== '') {
                        saveMemoRow(this, saveBtn, container);
                    }
                } else if (e.key === 'Escape') {
                    closeQuickAdd();
                }
            });

            actions.appendChild(alarmBtn);
            actions.appendChild(saveBtn);

            row.appendChild(input);
            row.appendChild(actions);
            container.appendChild(row);

            if (focus) input.focus();
        }

        function saveMemoRow(input, btnIcon, container) {
            const row = input.parentElement;
            const title = input.value.trim();
            const dateVal = document.getElementById('qa_current_date').value;
            const alarmMin = row.dataset.alarm;

            // Visual Pending
            btnIcon.className = 'fas fa-spinner fa-spin memo-icon';
            input.disabled = true;

            const formData = new FormData();
            formData.append('mode', 'bg_insert');
            formData.append('title', title);
            formData.append('start_datetime', dateVal + ' 09:00:00');
            formData.append('end_datetime', dateVal + ' 10:00:00');
            formData.append('color', '#3b82f6');
            formData.append('alarm_minutes', alarmMin);

            fetch('./admin_schedule_update.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Success UI
                        btnIcon.className = 'fas fa-check memo-icon text-green-500';
                        input.style.color = '#10b981';

                        // Add to Calendar
                        calendar.addEvent({
                            id: data.id,
                            title: title,
                            start: dateVal + ' 09:00:00',
                            end: dateVal + ' 10:00:00',
                            backgroundColor: '#3b82f6',
                            extendedProps: { is_done: 0, alarm_minutes: alarmMin }
                        });

                        // Focus Next
                        const nextRow = row.nextElementSibling;
                        if (nextRow && nextRow.classList.contains('memo-row')) {
                            const nextInput = nextRow.querySelector('input');
                            if (nextInput) nextInput.focus();
                        } else {
                            addMemoRow(container, true);
                        }
                    } else {
                        btnIcon.className = 'fas fa-exclamation memo-icon text-red-500';
                        input.disabled = false;
                        input.focus();
                    }
                });
        }

        // --- End Multi-line Logic ---

        // Colors for picker (Business Tones)
        const colors = [
            { t: '파랑', c: '#3b82f6', bg: '#eff6ff' },
            { t: '초록', c: '#10b981', bg: '#ecfdf5' },
            { t: '빨강', c: '#ef4444', bg: '#fef2f2' },
            { t: '노랑', c: '#f59e0b', bg: '#fffbeb' },
            { t: '보라', c: '#8b5cf6', bg: '#f5f3ff' },
            { t: '회색', c: '#6b7280', bg: '#f9fafb' },
        ];

        // Init Color Picker
        const pickerContainer = document.getElementById('color_picker');
        colors.forEach(col => {
            const dot = document.createElement('div');
            dot.className = 'w-6 h-6 rounded-full cursor-pointer border-2 border-transparent hover:scale-110 transition shadow-sm';
            dot.style.backgroundColor = col.c;
            dot.onclick = function () {
                document.getElementById('dm_color').value = col.c;
                Array.from(pickerContainer.children).forEach(c => {
                    c.style.borderColor = 'transparent';
                    c.style.transform = 'scale(1)';
                });
                dot.style.borderColor = '#333';
                dot.style.transform = 'scale(1.1)';
            };
            pickerContainer.appendChild(dot);
        });

        // Initialize Calendar
        var calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: 'dayGridMonth',
            height: '100%',
            expandRows: true,
            locale: 'ko',
            dayMaxEvents: 4, // Requests at least 4 visible
            headerToolbar: {
                left: 'title',
                center: '',
                right: 'prev,today,next dayGridMonth,timeGridWeek,timeGridDay'
            },
            buttonText: {
                today: '오늘',
                month: '월',
                week: '주',
                day: '일'
            },
            // Google Holidays
            googleCalendarApiKey: '',
            eventSources: [
                {
                    googleCalendarId: 'ko.south_korea#holiday@group.v.calendar.google.com',
                    className: 'gcal-event',
                    display: 'block',
                    backgroundColor: 'transparent',
                    borderColor: 'transparent',
                    textColor: '#ef4444',
                },
                {
                    url: './admin_schedule_list.php',
                    method: 'GET',
                    failure: function () { console.error('Load failed'); }
                }
            ],

            // Interaction
            editable: true,
            selectable: true,

            // Custom Rendering with Checkbox
            eventContent: function (arg) {
                let isDone = arg.event.extendedProps.is_done == 1;
                let color = arg.event.backgroundColor || '#3788d8';

                // Determine light BG color based on main color (approximate)
                // Fallback to white if no match found
                let bgColor = '#ffffff';
                let match = colors.find(c => c.c.toLowerCase() === color.toLowerCase());
                if (match) bgColor = match.bg;

                let container = document.createElement('div');
                container.className = 'fc-event-main';
                if (isDone) container.classList.add('done-task-inner');
                container.style.backgroundColor = bgColor;
                container.style.border = '1px solid ' + (match ? 'transparent' : '#eee'); // Add border if generic

                // 1. Color Strip
                let bar = document.createElement('div');
                bar.className = 'event-bar-strip';
                bar.style.backgroundColor = color;

                // 2. Checkbox
                let checkbox = document.createElement('input');
                checkbox.type = 'checkbox';
                checkbox.className = 'event-checkbox';
                checkbox.checked = isDone;
                // Click Handler
                checkbox.addEventListener('click', function (e) {
                    e.stopPropagation(); // Stop Modal Open
                    const newStatus = this.checked ? 1 : 0;
                    toggleEventDone(arg.event, newStatus);
                });

                // 3. Title
                let title = document.createElement('div');
                title.className = 'event-title-text';
                title.innerText = arg.event.title;
                if (isDone) title.style.textDecoration = 'line-through';

                // 4. Delete Icon (Absolute Position)
                let delBtn = document.createElement('div'); // Changed to div for easier flex centering
                delBtn.className = 'event-delete-btn';
                delBtn.innerHTML = '<i class="fas fa-times"></i>';
                delBtn.title = '삭제';

                // CRITICAL: Stop propagation chain immediately
                const stopProp = function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    e.stopImmediatePropagation();
                };

                // Prevent FullCalendar from seeing the click or mousedown
                delBtn.addEventListener('mousedown', stopProp);
                delBtn.addEventListener('click', function (e) {
                    stopProp(e);
                    if (confirm('이 일정을 삭제하시겠습니까?')) {
                        deleteEventDirect(arg.event.id);
                    }
                });

                container.appendChild(bar);
                container.appendChild(checkbox);
                container.appendChild(title);
                container.appendChild(delBtn);

                return { domNodes: [container] };
            },

            eventClassNames: function (arg) {
                if (arg.event.extendedProps.is_done == 1) {
                    return ['done-task'];
                }
                return [];
            },

            select: function (info) {
                showQuickAdd(info);
            },

            eventClick: function (info) {
                // Ignore delete button clicks (handled exclusively by button)
                if (info.jsEvent.target.closest('.event-delete-btn')) {
                    return;
                }

                // Ignore Google Calendar events
                if (info.event.display === 'block' && !info.event.id) return; // Holiday guess
                if (info.event.source && info.event.source.internalEventSource && info.event.source.internalEventSource.meta && info.event.source.internalEventSource.meta.googleCalendarId) return;

                showDetailModal(info);
            },

            eventDrop: function (info) { updateEventDate(info.event); },
            eventResize: function (info) { updateEventDate(info.event); }
        });

        calendar.render();
        window.calendar = calendar;

        // Toggle Done Logic
        window.toggleEventDone = function (event, status) {
            // Optimistic UI Update
            event.setExtendedProp('is_done', status);

            const formData = new FormData();
            formData.append('mode', 'toggle_done');
            formData.append('id', event.id);
            formData.append('is_done', status);

            fetch('./admin_schedule_update.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.status !== 'success') {
                        // Revert on failure
                        event.setExtendedProp('is_done', !status);
                        alert('상태 변경 실패');
                    }
                });
        };

        // Direct Delete Logic
        window.deleteEventDirect = function (id) {
            const formData = new FormData();
            formData.append('mode', 'delete');
            formData.append('id', id);

            fetch('./admin_schedule_update.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        calendar.getEventById(id).remove();
                    } else {
                        alert('삭제 실패');
                    }
                });
        };

        // --- Logic Functions ---

        window.submitDetail = function (e) {
            e.preventDefault();
            const form = document.getElementById('detailForm');
            const formData = new FormData(form);
            formData.append('mode', 'update');

            // Handle unchecked checkbox
            if (!document.getElementById('dm_is_done').checked) {
                formData.append('is_done', '0');
            }

            fetch('./admin_schedule_update.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    if (data.status === 'success') {
                        // Update UI immediately for speed sensation, then refetch for data integrity
                        let event = calendar.getEventById(document.getElementById('dm_id').value);
                        if (event) {
                            event.setProp('title', document.getElementById('dm_title').value);
                            event.setExtendedProp('is_done', document.getElementById('dm_is_done').checked ? 1 : 0);
                            event.setProp('backgroundColor', document.getElementById('dm_color').value);
                        }
                        calendar.refetchEvents();
                        closeDetailModal();
                    }
                });
            return false;
        };

        window.updateEventDate = function (event) {
            const formData = new FormData();
            formData.append('mode', 'update_date');
            formData.append('id', event.id);
            formData.append('start_datetime', event.start.toISOString());
            formData.append('end_datetime', event.end ? event.end.toISOString() : event.start.toISOString());

            fetch('./admin_schedule_update.php', { method: 'POST', body: formData });
        };

        window.deleteEvent = function () {
            if (!confirm('정말 삭제하시겠습니까?')) return;
            const id = document.getElementById('dm_id').value;
            const formData = new FormData();
            formData.append('mode', 'delete');
            formData.append('id', id);

            fetch('./admin_schedule_update.php', { method: 'POST', body: formData })
                .then(res => res.json())
                .then(data => {
                    calendar.refetchEvents();
                    closeDetailModal();
                });
        };

        // --- UI Functions ---

        function showQuickAdd(info) {
            closeDetailModal();
            const pop = document.getElementById('quickAddPopover');

            document.getElementById('qa_current_date').value = info.startStr;
            document.getElementById('qa_date_display').innerText = info.startStr;

            // Positioning
            pop.style.display = 'block';
            pop.style.top = '20%';
            pop.style.left = '50%';
            pop.style.transform = 'translate(-50%, 0)';

            // Initialize/Reset inputs
            initMemoInputs();
        }

        window.closeQuickAdd = function () {
            document.getElementById('quickAddPopover').style.display = 'none';
        }

        function showDetailModal(info) {
            closeQuickAdd();
            const modal = document.getElementById('detailModal');
            const event = info.event;
            const props = event.extendedProps;

            document.getElementById('dm_id').value = event.id;
            document.getElementById('dm_title').value = event.title;
            // Visual toggle check
            const isDone = (props.is_done == 1);
            document.getElementById('dm_is_done').checked = isDone;
            toggleStrike(document.getElementById('dm_is_done')); // Update visual state of input

            document.getElementById('dm_start').value = toLocalISOString(event.start);
            document.getElementById('dm_end').value = event.end ? toLocalISOString(event.end) : toLocalISOString(event.start);

            document.getElementById('dm_memo').value = props.memo || '';
            document.getElementById('dm_color').value = event.backgroundColor;
            document.getElementById('dm_alarm').value = props.alarm_minutes || 0;

            // Memo state reset or open if has content
            const memoContainer = document.getElementById('memo_container');
            const memoText = document.getElementById('memo_toggle_text');
            if (props.memo) {
                memoContainer.style.display = 'block';
                memoText.innerText = '접기';
            } else {
                memoContainer.style.display = 'none';
                memoText.innerText = '펼치기';
            }

            modal.showModal();
        }

        window.closeDetailModal = function () {
            document.getElementById('detailModal').close();
        }

        window.toggleMemo = function () {
            const container = document.getElementById('memo_container');
            const text = document.getElementById('memo_toggle_text');
            if (container.style.display === 'none') {
                container.style.display = 'block';
                text.innerText = '접기';
            } else {
                container.style.display = 'none';
                text.innerText = '펼치기';
            }
        }

        window.toggleStrike = function (checkbox) {
            const input = document.getElementById('dm_title');
            if (checkbox.checked) {
                input.style.textDecoration = 'line-through';
                input.style.color = '#9ca3af';
            } else {
                input.style.textDecoration = 'none';
                input.style.color = '#000';
            }
        }

        // --- Utilities ---
        function toLocalISOString(date) {
            const offset = date.getTimezoneOffset() * 60000;
            return new Date(date.getTime() - offset).toISOString().slice(0, 16);
        }
    });
</script>

<?php
if (!$is_app_mode) {
    include_once('./admin.tail.php');
} else {
    include_once(G5_PATH . '/tail.sub.php');
}
?>