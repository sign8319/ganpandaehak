/**
 * Admin Quote System Common JavaScript
 * - Real-time synchronization
 * - Toast notifications
 * - Auto-save
 */

const QuoteSync = {
    qa_id: 0,
    syncTimer: null,

    init: function (qa_id) {
        this.qa_id = qa_id;
        this.bindEvents();
        console.log('QuoteSync initialized for QA_ID:', qa_id);
    },

    bindEvents: function () {
        const self = this;
        // Bind to all inputs that should be synced
        $(document).on('change', 'input[name^="qa_"], select[name^="qa_"], textarea[name^="qa_"]', function () {
            const $this = $(this);
            const name = $this.attr('name');
            const value = $this.val();

            // Skip some fields if necessary
            if (name === 'token' || name === 'w') return;

            self.syncField(name, value, $this);
        });
    },

    syncField: function (name, value, $el) {
        const self = this;

        // Show loading spinner in the parent section if available
        const $section = $el.closest('div');
        const $spinner = $('<i class="fas fa-spinner fa-spin text-orange-500 ml-2 sync-spinner"></i>');

        $el.parent().find('.sync-spinner').remove();
        $el.after($spinner);

        $.ajax({
            url: './ajax_quote_autosave.php',
            type: 'POST',
            data: {
                qa_id: self.qa_id,
                field: name,
                value: value
            },
            dataType: 'json',
            success: function (res) {
                $spinner.fadeOut(200, function () { $(this).remove(); });
                if (res.success) {
                    if (res.is_new) {
                        self.qa_id = res.qa_id;
                        // Update URL with new qa_id if it was 0
                        const url = new URL(window.location.href);
                        url.searchParams.set('qa_id', res.qa_id);
                        window.history.replaceState({}, '', url);

                        // Update hidden fields in form
                        $('input[name="qa_id"]').val(res.qa_id);
                    }
                    self.showToast("변경사항이 반영되었습니다.");
                    self.updateStepBadges();
                } else {
                    console.error('Sync failed:', res.message);
                }
            },
            error: function () {
                $spinner.remove();
                self.showToast("저장에 실패했습니다. 다시 시도해주세요.", "error");
            }
        });
    },

    showToast: function (msg, type = "success") {
        $('.quote-toast').remove();
        const bgColor = type === "success" ? "bg-gray-900" : "bg-red-600";
        const $toast = $('<div class="quote-toast fixed top-10 left-1/2 transform -translate-x-1/2 ' + bgColor + ' text-white px-6 py-3 rounded-full shadow-2xl z-[9999] transition-all duration-300 opacity-0 text-sm font-bold flex items-center gap-2">' +
            (type === "success" ? '<i class="fas fa-check-circle text-green-400"></i>' : '<i class="fas fa-exclamation-circle"></i>') +
            '<span>' + msg + '</span></div>');

        $('body').append($toast);
        setTimeout(() => $toast.removeClass('opacity-0').addClass('opacity-100'), 100);
        setTimeout(() => {
            $toast.addClass('opacity-0');
            setTimeout(() => $toast.remove(), 300);
        }, 2000);
    },

    updateStepBadges: function () {
        // Logic to check mandatory fields and update step bar badges
        // This will be called after each sync
        this.checkStepStatus();
    },

    checkStepStatus: function () {
        const steps = [
            { id: 1, fields: ['qa_subject', 'qa_client_name', 'qa_client_hp'] },
            { id: 2, fields: [] }, // Will need item count check
            { id: 3, fields: [] }
        ];

        // Simplified check for now
        steps.forEach(step => {
            let isComplete = true;
            step.fields.forEach(f => {
                if (!$('input[name="' + f + '"]').val()) isComplete = false;
            });

            const $badge = $('.step-item[data-step="' + step.id + '"] .incomplete-badge');
            if (!isComplete) {
                if ($badge.length === 0) {
                    $('.step-item[data-step="' + step.id + '"]').append('<span class="incomplete-badge absolute top-1 right-1 w-2 h-2 bg-orange-500 rounded-full" title="미완료 항목이 있습니다"></span>');
                }
            } else {
                $badge.remove();
            }
        });
    }
};

// Integrate into step bar
function navigateToPage(url) {
    // Optional: Check for unsaved changes or force save before leaving
    location.href = url;
}
