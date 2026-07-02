<template>
  <!-- Component is programmatic; no visible DOM required -->
  <div style="display:none"></div>
</template>

<script setup>
import { onMounted } from 'vue'
import { useI18n } from 'vue-i18n'
import Swal from 'sweetalert2'

const { t, locale } = useI18n()

function setLang(lang) {
  locale.value = lang
  localStorage.setItem('locale', lang)
  if (lang === 'ar') {
    document.documentElement.setAttribute('dir', 'rtl')
    document.documentElement.setAttribute('lang', 'ar')
    return
  }
  document.documentElement.setAttribute('dir', 'ltr')
  document.documentElement.setAttribute('lang', lang)
}

onMounted(() => {
  const savedLang = localStorage.getItem('locale') || locale.value
  setLang(savedLang)
})

const confirmColor = 'rgb(175 81 102 / var(--tw-bg-opacity, 1))'

async function open() {
  const result = await Swal.fire({
    title: t('ratingModal.title'),
    confirmButtonColor: confirmColor,
    html: `
      <style>
        .swal-star { font-size: 28px; cursor: pointer; color: #d1d5db; }
        .swal-star.selected { color: #f59e0b; }
        .swal-stars { display:flex; gap:6px; justify-content:center; margin-bottom:10px }
        textarea.swal-feedback { width:100%; min-height:90px; padding:8px; border-radius:6px; border:1px solid #e5e7eb }
        .swal2-html-container { direction: ${locale.value === 'ar' ? 'rtl' : 'ltr'}; }
        .swal-stars { direction: ltr; }
        textarea.swal-feedback { direction: ${locale.value === 'ar' ? 'rtl' : 'ltr'}; }
      </style>
      <div class="swal-stars">
        <span class="swal-star" data-value="1">★</span>
        <span class="swal-star" data-value="2">★</span>
        <span class="swal-star" data-value="3">★</span>
        <span class="swal-star" data-value="4">★</span>
        <span class="swal-star" data-value="5">★</span>
      </div>
      <input type="hidden" id="swal-rating" />
      <textarea id="swal-feedback" class="swal-feedback" placeholder="${t('ratingModal.feedbackPlaceholder')}"></textarea>
    `,
    showCancelButton: true,
    confirmButtonText: t('ratingModal.submitRating'),
    cancelButtonText: t('ratingModal.skip'),
    focusConfirm: false,
    preConfirm: () => {
      const ratingEl = document.getElementById('swal-rating')
      const rating = ratingEl ? ratingEl.value : ''
      const comment = document.getElementById('swal-feedback')?.value || ''
      if (!rating) {
        Swal.showValidationMessage(t('ratingModal.validationMessage'))
        return false
      }
      return { rating: Number(rating), comment: String(comment || '') }
    },
    didOpen: () => {
      const stars = document.querySelectorAll('.swal-star')
      stars.forEach((s) => {
        s.addEventListener('click', () => {
          const v = s.getAttribute('data-value')
          document.getElementById('swal-rating').value = v || ''
          stars.forEach((x) => x.classList.remove('selected'))
          for (const x of stars) {
            if (Number(x.getAttribute('data-value')) <= Number(v)) x.classList.add('selected')
          }
        })
      })
    }
  })

  return result?.value || null
}

defineExpose({ open })
</script>

<style scoped>
/* no styles */
</style>