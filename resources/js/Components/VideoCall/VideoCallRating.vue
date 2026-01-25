<template>
  <!-- Component is programmatic; no visible DOM required -->
  <div style="display:none"></div>
</template>

<script setup>
import Swal from 'sweetalert2'
import { defineExpose } from 'vue'

const confirmColor = 'rgb(175 81 102 / var(--tw-bg-opacity, 1))'

async function open() {
  const result = await Swal.fire({
    title: 'Rate this session',
    confirmButtonColor: confirmColor,
    html: `
      <style>
        .swal-star { font-size: 28px; cursor: pointer; color: #d1d5db; }
        .swal-star.selected { color: #f59e0b; }
        .swal-stars { display:flex; gap:6px; justify-content:center; margin-bottom:10px }
        textarea.swal-feedback { width:100%; min-height:90px; padding:8px; border-radius:6px; border:1px solid #e5e7eb }
      </style>
      <div class="swal-stars">
        <span class="swal-star" data-value="1">★</span>
        <span class="swal-star" data-value="2">★</span>
        <span class="swal-star" data-value="3">★</span>
        <span class="swal-star" data-value="4">★</span>
        <span class="swal-star" data-value="5">★</span>
      </div>
      <input type="hidden" id="swal-rating" />
      <textarea id="swal-feedback" class="swal-feedback" placeholder="Optional feedback (what went well, suggestions, issues)"></textarea>
    `,
    showCancelButton: true,
    confirmButtonText: 'Submit rating',
    cancelButtonText: 'Skip',
    focusConfirm: false,
    preConfirm: () => {
      const ratingEl = document.getElementById('swal-rating')
      const rating = ratingEl ? ratingEl.value : ''
      const comment = document.getElementById('swal-feedback')?.value || ''
      if (!rating) {
        Swal.showValidationMessage('Please select a rating (1–5) or press Skip')
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
