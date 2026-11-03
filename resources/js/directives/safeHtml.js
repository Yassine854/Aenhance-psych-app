import DOMPurify from 'dompurify'

function renderSafeHtml(el, binding) {
  el.innerHTML = DOMPurify.sanitize(String(binding.value ?? ''), {
    USE_PROFILES: { html: true },
    FORBID_TAGS: ['style', 'iframe', 'form', 'input', 'button', 'textarea'],
  })
}

export default {
  beforeMount: renderSafeHtml,
  updated(el, binding) {
    if (binding.value !== binding.oldValue) renderSafeHtml(el, binding)
  },
}
