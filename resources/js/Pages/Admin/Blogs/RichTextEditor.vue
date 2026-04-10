<template>
  <div class="overflow-hidden rounded-3xl border border-gray-200 bg-white shadow-sm">
    <div class="border-b border-gray-200 bg-[linear-gradient(180deg,#fbfbfb_0%,#f4f4f5_100%)] p-4">
      <div ref="toolbarElement" class="quill-toolbar">
        <span class="ql-formats">
          <select class="ql-header">
            <option selected></option>
            <option value="2"></option>
            <option value="3"></option>
          </select>
        </span>

        <span class="ql-formats">
          <button class="ql-bold"></button>
          <button class="ql-italic"></button>
          <button class="ql-underline"></button>
        </span>

        <span class="ql-formats">
          <button class="ql-list" value="ordered"></button>
          <button class="ql-list" value="bullet"></button>
          <button class="ql-blockquote"></button>
        </span>

        <span class="ql-formats">
          <button class="ql-link"></button>
          <button class="ql-clean"></button>
        </span>
      </div>
    </div>

    <div class="bg-white p-4">
      <div ref="editorElement" class="quill-editor-shell"></div>
      <p v-if="error" class="mt-3 text-sm text-red-600">{{ error }}</p>
    </div>
  </div>
</template>

<script setup>
import { nextTick, onBeforeUnmount, onMounted, ref, watch } from 'vue'
import Quill from 'quill'
import 'quill/dist/quill.snow.css'

const props = defineProps({
  modelValue: { type: String, default: '' },
  placeholder: { type: String, default: 'Start writing your article...' },
  error: { type: String, default: '' },
})

const emit = defineEmits(['update:modelValue'])

const editorElement = ref(null)
const toolbarElement = ref(null)
let quillInstance = null
let isApplyingExternalValue = false

function normalizeHtml(value) {
  const html = String(value || '').trim()
  return html === '<p><br></p>' ? '' : html
}

function getEditorHtml() {
  if (!quillInstance) return ''
  return normalizeHtml(quillInstance.root.innerHTML)
}

function applyHtml(value) {
  if (!quillInstance) return

  const nextValue = normalizeHtml(value)
  const currentValue = getEditorHtml()
  if (currentValue === nextValue) return

  isApplyingExternalValue = true
  quillInstance.setContents([])
  if (nextValue) {
    quillInstance.clipboard.dangerouslyPasteHTML(nextValue)
  }
  isApplyingExternalValue = false
}

function handleTextChange() {
  if (!quillInstance || isApplyingExternalValue) return
  emit('update:modelValue', getEditorHtml())
}

watch(() => props.modelValue, (value) => {
  applyHtml(value)
})

onMounted(async () => {
  await nextTick()

  if (!editorElement.value || !toolbarElement.value) return

  quillInstance = new Quill(editorElement.value, {
    theme: 'snow',
    placeholder: props.placeholder,
    modules: {
      toolbar: toolbarElement.value,
      history: {
        delay: 400,
        maxStack: 100,
        userOnly: true,
      },
    },
  })

  applyHtml(props.modelValue)
  quillInstance.on('text-change', handleTextChange)
})

onBeforeUnmount(() => {
  if (quillInstance) {
    quillInstance.off('text-change', handleTextChange)
    quillInstance = null
  }
})
</script>

<style scoped>
.quill-toolbar :deep(.ql-toolbar.ql-snow) {
  border: 0;
  padding: 0;
}

.quill-editor-shell :deep(.ql-container.ql-snow) {
  border: 0;
}

.quill-editor-shell :deep(.ql-editor) {
  background: rgb(249 250 251 / 0.65);
  border: 1px solid rgb(229 231 235);
  border-radius: 1.5rem;
  color: rgb(31 41 55);
  font-size: 0.95rem;
  line-height: 1.9;
  min-height: 460px;
  padding: 1.4rem 1.5rem;
  transition: border-color 150ms ease, box-shadow 150ms ease, background-color 150ms ease;
}

.quill-editor-shell :deep(.ql-editor.ql-blank::before) {
  color: rgb(156 163 175);
  font-style: normal;
  left: 1.5rem;
  right: 1.5rem;
}

.quill-editor-shell :deep(.ql-editor:focus) {
  background: white;
  border-color: rgb(89 151 172);
  box-shadow: 0 0 0 3px rgb(89 151 172 / 0.12);
}

.quill-toolbar :deep(.ql-picker),
.quill-toolbar :deep(.ql-picker-label),
.quill-toolbar :deep(.ql-picker-item),
.quill-toolbar :deep(button) {
  color: rgb(55 65 81);
}

.quill-toolbar :deep(button),
.quill-toolbar :deep(.ql-picker-label) {
  border-radius: 0.85rem;
  transition: background-color 150ms ease, color 150ms ease;
}

.quill-toolbar :deep(button:hover),
.quill-toolbar :deep(.ql-picker-label:hover) {
  background: rgb(243 244 246);
}

.quill-toolbar :deep(.ql-active) {
  color: rgb(89 151 172);
}

.quill-editor-shell :deep(.ql-editor h2) {
  font-size: 1.7rem;
  font-weight: 700;
  line-height: 2.15rem;
  margin: 1.25rem 0 0.85rem;
}

.quill-editor-shell :deep(.ql-editor h3) {
  font-size: 1.32rem;
  font-weight: 700;
  line-height: 1.85rem;
  margin: 1.1rem 0 0.75rem;
}

.quill-editor-shell :deep(.ql-editor blockquote) {
  border-left: 4px solid rgb(89 151 172);
  color: rgb(75 85 99);
  margin: 1rem 0;
  padding-left: 1rem;
}

.quill-editor-shell :deep(.ql-editor a) {
  color: rgb(14 116 144);
  text-decoration: underline;
}
</style>