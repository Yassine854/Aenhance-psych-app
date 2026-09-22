// Small utility to detect if a string contains Arabic (or other RTL script) characters.
// Returns true when the first strong character in the string is an RTL character.
export function isRtl(value = '') {
  if (!value) return false

  // Strip HTML if any (safe-guard when content is HTML)
  const text = String(value).replace(/<[^>]*>/g, '').trim()
  if (!text) return false

  // Find first strong character
  for (let i = 0; i < text.length; i++) {
    const ch = text[i]
    if (/\s/.test(ch)) continue

    // Arabic and related ranges: \u0600-\u06FF, \u0750-\u077F, \u08A0-\u08FF,
    // Arabic Presentation Forms, and others used by RTL languages.
    if (/[\u0590-\u05FF\u0600-\u06FF\u0750-\u077F\u08A0-\u08FF\uFB1D-\uFDFF\uFE70-\uFEFF]/.test(ch)) {
      return true
    }

    // If we hit a Latin/other strong LTR char, stop and return false
    if (/[A-Za-z0-9]/.test(ch)) return false
  }

  return false
}
