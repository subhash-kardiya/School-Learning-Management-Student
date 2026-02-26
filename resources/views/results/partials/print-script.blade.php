@once
@push('css')
<style>
    body.res-print-page .sidebar,
    body.res-print-page .main-content-header,
    body.res-print-page .res-title-bar,
    body.res-print-page .js-print-trigger,
    body.res-print-page .res-status-badge,
    body.res-print-page .btn,
    body.res-print-page .res-stat,
    body.res-print-page .res-stats-row,
    body.res-print-page .res-filter-card,
    body.res-print-page form {
        display: none !important;
    }

    body.res-print-page .main-content {
        margin: 0 !important;
        padding: 0 !important;
        width: 100% !important;
    }

    body.res-print-page .res-shell {
        padding: 0 !important;
    }

    body.res-print-page .table-responsive,
    body.res-print-page .res-table-wrap,
    body.res-print-page .res-teacher-no-scroll {
        overflow: visible !important;
        max-height: none !important;
        height: auto !important;
    }

    @media print {
        @page {
            size: A4 portrait;
            margin: 6mm;
        }

        * {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        html,
        body {
            height: auto !important;
            min-height: 0 !important;
            overflow: visible !important;
            background: #fff !important;
        }

        .sidebar,
        .main-content-header,
        .js-print-trigger,
        .res-status-badge,
        .res-title-bar,
        .btn,
        .res-stat,
        .res-stats-row,
        .res-filter-card,
        form,
        nav {
            display: none !important;
        }

        .main-content {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            min-height: 0 !important;
            height: auto !important;
        }

        .res-shell {
            padding: 0 !important;
            margin: 0 !important;
            min-height: 0 !important;
            height: auto !important;
            border-radius: 0 !important;
        }

        .table-responsive,
        .res-table-wrap,
        .res-teacher-no-scroll {
            overflow: visible !important;
            max-height: none !important;
            height: auto !important;
        }

        .card {
            break-inside: auto !important;
            page-break-inside: auto !important;
            box-shadow: none !important;
        }

        .container-fluid {
            min-height: 0 !important;
            height: auto !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
        }

        body.res-student-print .res-shell {
            zoom: 0.84;
            transform-origin: top left;
        }

        body.res-student-print .card,
        body.res-student-print .res-total-box {
            margin-bottom: 6px !important;
            padding-top: 8px !important;
            padding-bottom: 8px !important;
        }
    }
</style>
@endpush

@push('scripts')
<script>
    (function () {
        document.addEventListener('click', function (event) {
            const btn = event.target.closest('.js-print-trigger');
            if (!btn) return;
            event.preventDefault();

            // For student results page: hide sidebar/header/filters, print, then restore
            if (window.location.pathname.includes('/student/results')) {
                document.body.classList.add('res-print-page', 'res-student-print');
                window.print();
                document.body.classList.remove('res-print-page', 'res-student-print');
                return;
            }

            // For AJAX tables (teacher view), fetch all data then print inline.
            const ajaxTable = document.getElementById('teacher-result-details-table');
            if (ajaxTable && window.jQuery && $.fn.DataTable.isDataTable(ajaxTable)) {
                const dt = $(ajaxTable).DataTable();
                const originalUrl = dt.ajax.url();
                const params = dt.ajax.params();
                params.length = -1;

                const tempMessage = document.createElement('div');
                tempMessage.className = 'alert alert-info';
                tempMessage.textContent = 'Preparing print view...';
                ajaxTable.parentElement.insertBefore(tempMessage, ajaxTable);

                $.get(originalUrl, params).done(function(json) {
                    dt.clear();
                    dt.rows.add(json.data || []);
                    dt.draw();
                    $('.dataTables_wrapper .row').hide();
                    tempMessage.remove();

                    document.body.classList.add('res-print-page');
                    window.print();
                    document.body.classList.remove('res-print-page');
                    $('.dataTables_wrapper .row').show();
                    dt.page.len(10).draw();
                }).fail(function() {
                    alert('Could not load all data for printing.');
                    tempMessage.remove();
                });
            } else {
                document.body.classList.add('res-print-page');
                window.print();
                document.body.classList.remove('res-print-page');
            }
        });
    })();
</script>
@endpush
@endonce
