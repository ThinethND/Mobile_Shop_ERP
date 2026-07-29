<template>
  <div class="dt-wrapper overflow-hidden">
    <!-- Table -->
    <div class="dt-table">
      <!-- Initial Loader (before DataTable finishes loading) -->
      <div v-if="isLoading" class="dt-initial-loader">
        <div class="dt-loading-overlay">
          <div class="dt-spinner"></div>
          <div class="dt-loading-text">Loading data...</div>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table :id="id" class="w-full text-sm text-neutral-700 dark:text-neutral-300">
          <thead class="bg-neutral-50 dark:bg-neutral-800 text-neutral-600 dark:text-neutral-300">
            <slot name="header"></slot>
          </thead>
          <tbody></tbody>
        </table>
      </div>
    </div>
  </div>
</template>

<script>
import { router } from '@inertiajs/vue3'

export default {
  name: 'DataTable',
  props: {
    id: { type: String, required: true },
    url: { type: String, required: true },
    columns: { type: Array, required: true },
    columnDefs: { type: Array, default: () => [] },
    order: { type: Array, default: () => [] },
    buttons: { type: Array, default: () => [] },
    method: { type: String, default: 'GET' },
    submitData: { type: Object, default: () => ({}) },
    reloadKey: { type: [Number, String, Boolean], default: null },

    dom: {
      type: String, default: `
  <'dt-toolbar flex flex-wrap items-center justify-end mb-3'
    <'dt-export flex items-center gap-2 mb-2'B>
  >
  <'dt-controls flex flex-wrap items-center justify-between mb-3'
    <'dt-left flex items-center gap-3'f l>
  >
  <'table-responsive drag't>
  <'dt-footer flex flex-wrap items-center justify-between mt-3' i p>
` },

    displayLength: { type: Number, default: 10 },
    lengthMenu: { type: Array, default: () => [[10, 25, 50, 100], ['10', '25', '50', '100']] },
    processing: { type: Boolean, default: true },
    serverSide: { type: Boolean, default: true },
    responsive: { type: Boolean, default: true },
    stateSave: { type: Boolean, default: false },
  },

  data() {
    return {
      dt: null,
      isLoading: true, // 🔹 Show loader until DataTable is ready
    }
  },

  mounted() { this.initDataTable() },
  beforeUnmount() { this.destroyDataTable() },

  watch: {
    submitData: { deep: true, handler() { this.reloadDatatable() } },
    url() { this.reloadDatatable() },
    reloadKey() { this.reloadDatatable() },
  },

  methods: {
    csrfToken() {
      return this.$page?.props?.csrf_token ||
        document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
    },

    initDataTable() {
      const $ = window.jQuery
      if (!$ || !$.fn?.DataTable) {
        console.error('[DataTable] jQuery/DataTables not found.')
        this.isLoading = false // stop loader if DataTable can't init
        return
      }

      const tableId = `#${this.id}`
      this.destroyDataTable()

      //           const btns = this.buttons.length ? this.buttons : [
      //     { extend: 'excel', className: 'dt-btn', text: 'Export Excel' },
      //     { extend: 'csv', className: 'dt-btn', text: 'Export CSV' },
      //     { extend: 'print', className: 'dt-btn', text: 'Print Table' },
      // ]

      const defaultExportOptions = {

        columns: ':visible:not(:last-child)',
      }

      const btns = this.buttons.length ? this.buttons : [
        {
          extend: 'excel',
          className: 'dt-btn',
          text: 'Export Excel',
          exportOptions: defaultExportOptions,
        },
        {
          extend: 'csv',
          className: 'dt-btn',
          text: 'Export CSV',
          exportOptions: defaultExportOptions,
        },
        {
          extend: 'print',
          className: 'dt-btn',
          text: 'Print Table',
          exportOptions: defaultExportOptions,
        },
      ]

      this.dt = $(tableId).DataTable({
        dom: this.dom,
        buttons: btns,
        processing: this.processing,
        serverSide: this.serverSide,
        responsive: this.responsive,
        stateSave: this.stateSave,
        lengthMenu: this.lengthMenu,
        pageLength: this.displayLength,
        order: this.order,
        columnDefs: this.columnDefs,
        columns: this.columns,
        language: {
          search: '',
          lengthMenu: '_MENU_',
          zeroRecords: 'No records found',
          paginate: { previous: '‹', next: '›' },
          processing: `
  <div class="dt-loading-overlay">
    <div class="dt-spinner"></div>
    <div class="dt-loading-text">Loading data...</div>
  </div>
`,
        },
        ajax: {
          url: this.url,
          method: this.method,
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'X-CSRF-TOKEN': this.csrfToken(),
          },
          data: (d) => {
            Object.assign(d, this.submitData)
          },
        },
        initComplete: () => {
          const $filter = $(`${tableId}_filter`)
          const $input = $filter.find('input')

          $filter.addClass('mb-0 dt-filter')

          $input
            .addClass('dt-search-input')
            .attr('placeholder', 'Name, email or phone')

          const $wrapper = $('<div class="dt-search-wrapper"></div>')
          $input.appendTo($wrapper)
          $filter.empty().append($wrapper)

          $(`${tableId}_length select`).addClass('dt-length-select')

          // 🔹 Hide initial loader once DataTable is ready
          this.isLoading = false
        },

      })
    },

    destroyDataTable() {
      const $ = window.jQuery
      if (this.dt) { try { this.dt.destroy(true) } catch { } this.dt = null }
    },

    reloadDatatable() {
      const $ = window.jQuery
      if (!$ || !this.dt) return
      try {
        if (this.dt.ajax?.url && this.dt.ajax.url() !== this.url) {
          this.dt.ajax.url(this.url).load(null, false)
          return
        }

        this.dt.ajax.reload(null, false)
      } catch {
        this.initDataTable()
      }
    },

    getApi() { return this.dt },
  },
}
</script>

<style scoped>
/* Header */
:deep(table.dataTable thead th) {
  font-weight: 600;
  font-size: 0.875rem;
  text-align: left;
  padding: 16px 16px;
  border-bottom: 1px solid #e5e7eb;
  background-color: #fafafa;
  color: #4b5563;
  white-space: nowrap;
}

:deep(table.dataTable tbody td) {
  padding: 16px 16px !important;
}

/* Table rows */
:deep(table.dataTable tbody tr) {
  border-bottom: 1px solid #f3f4f6;
  transition: background-color 0.15s ease-in-out;
}

/* Use cell borders instead of row border */
:deep(table.dataTable tbody tr) {
  border-bottom: none;
  /* override previous rule */
}

/* Grid-style separators: each cell has a bottom + right border */
:deep(table.dataTable) {
  border-collapse: separate;
  border-spacing: 0;
}


:deep(table.dataTable tbody td) {
  border-bottom: 1px solid #e7e9ec;

}

/* No extra right border on last column */
:deep(table.dataTable thead th:last-child),
:deep(table.dataTable tbody td:last-child) {
  border-right: none;
}


/* ✨ Custom light hover (your preferred style) */
:deep(table.dataTable tbody tr:hover) {
  background-color: rgba(185, 185, 185, 0.329);
}

/* Table cells */
:deep(table.dataTable tbody td) {
  padding: 12px 16px;
}

:deep(.dataTables_filter) {
  margin: 0;
}

:deep(.dt-search-wrapper) {
  position: relative;
  display: block;
  width: 100%;
}

/* Search input */
:deep(.dt-search-input) {
  background-color: #ffffff;
  border: 1px solid #d4d4d8;
  border-radius: 9999px;
  padding: 8px 14px 8px 34px;
  font-size: 0.875rem;
  width: 100%;
  min-width: 12rem;
  max-width: 18rem;
  outline: none;
  color: #111827;
  box-shadow: none;
}

:deep(.dt-search-wrapper)::before {
  content: '';
  position: absolute;
  left: 12px;
  top: 50%;
  width: 14px;
  height: 14px;
  transform: translateY(-50%);
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23a3a3a3' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='11' cy='11' r='7'/%3E%3Cline x1='16.65' y1='16.65' x2='21' y2='21'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-size: 14px 14px;
  pointer-events: none;
}

:deep(.dt-search-input:focus) {
  border-color: #38bdf8;
  box-shadow: 0 0 0 1px rgba(56, 189, 248, 0.12);
}

:deep(.dt-controls .dataTables_length) {
  margin-left: 12px;
}

/* Dropdown (length select) */
:deep(.dt-length-select) {
  background-color: #f9fafb;
  border: 1px solid #e5e7eb;
  border-radius: 6px;
  padding: 6px 32px 6px 10px;
  font-size: 0.875rem;
  color: #374151;
  cursor: pointer;
}

:deep(.dt-length-select:hover) {
  border-color: #cbd5e1;
  background-color: #f3f4f6;
}

:deep(.dt-length-select:focus) {
  outline: none;
  border-color: #38bdf8;
  box-shadow: 0 0 0 0;
}

/* Custom option styling (limited but consistent) */
:deep(.dt-length-select option) {
  background-color: #ffffff;
  color: #111827;
  font-size: 0.875rem;
  padding: 6px 10px;
}

/* :deep(.dt-length-select option:hover) {
  background-color: #38bdf8 !important;
  color: #fff;
} */
/* Buttons */
:deep(.dt-btn) {
  border: 1px solid #38bdf8;
  color: #38bdf8;
  border-radius: 20px;
  padding: 6px 12px;
  font-size: 0.875rem;
  cursor: pointer;
}

:deep(.dt-btn:hover) {
  background-color: #38bdf8;
  color: #fff;
}

/* Pagination container */
:deep(.dataTables_wrapper .dataTables_paginate) {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 6px;
  margin-top: 16px;
}

/* Page buttons */

:deep(.paginate_button) {
  margin: 4px;
}

:deep(.dataTables_wrapper .dataTables_paginate .paginate_button) {
  display: inline-flex !important;
  align-items: center;
  justify-content: center;
  min-width: 34px;
  height: 34px;
  padding: 0 10px !important;
  border: 1px solid #e0e0e0 !important;
  border-radius: 9999px !important;
  /* full pill shape */
  background-color: #ffffff !important;
  color: #333333 !important;
  font-size: 0.875rem;
  font-weight: 500;
  cursor: pointer;
  transition: all 0.2s ease-in-out;
}

/* Hover effect */
:deep(.dataTables_wrapper .dataTables_paginate .paginate_button:hover) {
  background-color: #000000 !important;
  color: #fff !important;
}

/* Active (current) page */
:deep(.dataTables_wrapper .dataTables_paginate .paginate_button.active) {
  background-color: #38bdf8 !important;
  /* Indigo tone */
  border-color: #38bdf8 !important;
  color: #ffffff !important;
  font-weight: 600;
  box-shadow: 0 2px 4px rgba(79, 70, 229, 0.25);
}

/* Disabled arrows */
:deep(.dataTables_wrapper .dataTables_paginate .paginate_button.disabled) {
  opacity: 0.5;
  cursor: not-allowed;
}




/* Info text */
:deep(.dataTables_wrapper .dataTables_info) {
  color: #6b7280;
  font-size: 0.875rem;
  margin-top: 8px;
}

:deep(.dataTables_wrapper .dt-toolbar),
:deep(.dataTables_wrapper .dt-controls),
:deep(.dataTables_wrapper .dt-footer) {
  gap: 0.75rem;
}

:deep(.dataTables_wrapper .dataTables_filter),
:deep(.dataTables_wrapper .dataTables_length) {
  margin: 0;
}

:deep(.dataTables_wrapper .dt-export),
:deep(.dataTables_wrapper .dt-left) {
  display: flex;
  flex-wrap: wrap;
  gap: 0.75rem;
  width: 100%;
}

:deep(#usersTable) {
  width: 100% !important;
}


/* ===============================
   DataTable Loading Spinner
   =============================== */

:deep(.dataTables_processing) {
  position: absolute !important;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  display: flex !important;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(2px);
  z-index: 50;
  border-radius: 0.75rem;
}

/* Spinner container */
:deep(.dt-loading-overlay) {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 8px;
}

/* Spinner circle */
:deep(.dt-spinner) {
  width: 40px;
  height: 40px;
  border: 3px solid #d1d5db;
  border-top-color: #38bdf8;
  border-radius: 50%;
  animation: spin 0.7s linear infinite;
}

/* Loading text */
:deep(.dt-loading-text) {
  font-size: 0.875rem;
  color: #4b5563;
  margin-top: 6px;
}

/* Dark mode */
:root.dark :deep(.dataTables_processing) {
  background: rgba(17, 24, 39, 0.7);
}

:root.dark :deep(.dt-spinner) {
  border-color: #374151;
  border-top-color: #38bdf8;
}

:root.dark :deep(.dt-loading-text) {
  color: #d1d5db;
}

/* Spinner animation */
@keyframes spin {
  to {
    transform: rotate(360deg);
  }
}

/* 🔹 Position dt-table relative so the initial loader can overlay it */
.dt-table {
  position: relative;
}

/* 🔹 Initial loader overlay before DataTable is fully initialized */
.dt-initial-loader {
  position: absolute;
  inset: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  background: rgba(255, 255, 255, 0.6);
  backdrop-filter: blur(2px);
  z-index: 40;
  border-radius: 0.75rem;
}

:root.dark :deep(.dt-initial-loader) {
  background: rgba(17, 24, 39, 0.7);
}

/* =====================================
   RESPONSIVE STYLES (Mobile & Tablet)
   ===================================== */

/* Make table scrollable horizontally on smaller screens */
@media (max-width: 1024px) {
  :deep(.dt-table .overflow-x-auto) {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
  }

  /* Reduce padding for table cells */
  :deep(table.dataTable thead th),
  :deep(table.dataTable tbody td) {
    padding: 10px 12px;
  }

  /* Compact search and dropdown */
  :deep(.dataTables_wrapper .dt-toolbar),
  :deep(.dataTables_wrapper .dt-controls),
  :deep(.dataTables_wrapper .dt-footer) {
    flex-direction: column;
    align-items: flex-start;
    gap: 10px;
  }

  :deep(.dataTables_wrapper .dt-export),
  :deep(.dataTables_wrapper .dt-left),
  :deep(.dataTables_wrapper .dataTables_filter),
  :deep(.dataTables_wrapper .dataTables_length),
  :deep(.dataTables_wrapper .dataTables_info),
  :deep(.dataTables_wrapper .dataTables_paginate),
  :deep(.dataTables_wrapper .dt-buttons) {
    width: 100%;
  }

  :deep(.dt-search-input),
  :deep(.dt-length-select),
  :deep(.dataTables_wrapper .dt-btn) {
    width: 100%;
    max-width: 100%;
  }
}

/* Tablet view adjustments */
@media (max-width: 768px) {

  /* Make table more compact */
  :deep(table.dataTable thead th),
  :deep(table.dataTable tbody td) {
    font-size: 0.8125rem;
    padding: 8px 10px;
  }

  /* Stack pagination and info */
  :deep(.dataTables_wrapper .dataTables_paginate),
  :deep(.dataTables_wrapper .dataTables_info) {
    justify-content: center;
    text-align: center;
  }

  /* Reduce button padding */
  :deep(.dt-btn) {
    padding: 4px 10px;
    font-size: 0.8125rem;
  }

  /* Adjust pagination pills */
  :deep(.dataTables_wrapper .dataTables_paginate .paginate_button) {
    min-width: 30px;
    height: 30px;
    font-size: 0.8rem;
    padding: 0 8px !important;
  }
}

/* Mobile view (phones) */
@media (max-width: 640px) {

  /* Hide unnecessary columns on small screens if needed */
  :deep(table.dataTable td:nth-child(4)),
  :deep(table.dataTable th:nth-child(4)),
  :deep(table.dataTable td:nth-child(5)),
  :deep(table.dataTable th:nth-child(5)) {
    display: none;
    /* Optional — hide extra columns for mobile */
  }

  /* Tighter container spacing */
  :deep(.dt-wrapper) {
    border-radius: 12px;
  }

  /* Adjust top bar layout */
  :deep(.dt-topbar) {
    padding: 12px;
  }

  /* Center align pagination */
  :deep(.dataTables_wrapper .dataTables_paginate) {
    flex-wrap: wrap;
    justify-content: center;
    gap: 4px;
  }

  /* Smaller search bar */
  :deep(.dt-search-input) {
    width: 100%;
    font-size: 0.8rem;
  }
  
}

@media (max-width: 640px) {
  :deep(table.dataTable td:nth-child(4)),
  :deep(table.dataTable th:nth-child(4)),
  :deep(table.dataTable td:nth-child(5)),
  :deep(table.dataTable th:nth-child(5)) {
    display: table-cell;
  }

  :deep(.dataTables_wrapper .dt-toolbar),
  :deep(.dataTables_wrapper .dt-controls),
  :deep(.dataTables_wrapper .dt-footer) {
    align-items: stretch;
  }

  :deep(.dataTables_wrapper .dataTables_info) {
    text-align: center;
  }
}

/* Ultra small devices (under 400px) */
@media (max-width: 400px) {

  /* Compact buttons and text */
  :deep(.dt-btn) {
    padding: 3px 8px;
    font-size: 0.75rem;
  }

  :deep(.dataTables_wrapper .dataTables_paginate .paginate_button) {
    min-width: 26px;
    height: 26px;
    font-size: 0.75rem;
  }
}

:deep(.dt-search-input),
:deep(.dt-length-select) {
  border-radius: 9999px;
  height: 36px;
  font-size: 0.875rem;
}

/* Page-count dropdown specific styles */
:deep(.dt-length-select) {
  background-color: #ffffff;
  border: 1px solid #d4d4d8;
  padding: 8px 28px 8px 12px;
  color: #374151;
  cursor: pointer;
}
</style>
