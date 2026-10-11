export function initResponsiveCards() {
  const mediaQuery = window.matchMedia('(max-width: 600px)')
  const tableSelector = '.data-table, .table, .table-custom, table.data-table'

  function processAll() {
    document.querySelectorAll(tableSelector).forEach(table => {
      if (table.closest('.modal')) return
      if (table.closest('.cal-grid')) return
      if (table.closest('.timetable-grid')) return
      if (table.dataset.cardsProcessed) return
      table.dataset.cardsProcessed = '1'

      const thead = table.querySelector('thead')
      if (thead) {
        const headers = Array.from(thead.querySelectorAll('th')).map(th => th.textContent.trim())
        const rows = table.querySelectorAll('tbody tr')
        rows.forEach(row => {
          row.querySelectorAll('td').forEach((td, i) => {
            if (headers[i] && !td.hasAttribute('data-label')) {
              td.setAttribute('data-label', headers[i])
            }
          })
        })
      }

      table.classList.add('data-table')
    })
  }

  const observer = new MutationObserver(() => {
    processAll()
    toggleView()
  })
  observer.observe(document.body, { childList: true, subtree: true })

  function toggleView() {
    const isMobile = mediaQuery.matches
    document.querySelectorAll(tableSelector).forEach(table => {
      table.classList.toggle('mobile-cards', isMobile)
    })
  }

  function onMediaChange() { toggleView() }
  mediaQuery.addEventListener('change', onMediaChange)

  processAll()
  toggleView()

  return function cleanup() {
    observer.disconnect()
    mediaQuery.removeEventListener('change', onMediaChange)
  }
}
