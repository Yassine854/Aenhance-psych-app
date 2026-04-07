/**
 * Resolves a stored avatar value to a browser-accessible URL.
 *
 * Handles all forms the backend may return:
 *  - Full URL pointing to local storage (http://localhost/storage/...) →
 *      strip the foreign origin, keep only /storage/... path so the image
 *      loads on whatever host the browser is actually on.
 *  - Full URL to an external service (S3, Cloudinary, etc.) →
 *      returned as-is (no /storage/ in path).
 *  - Absolute path (/storage/...) → returned as-is.
 *  - Storage-relative path (avatars/...) → prefixed with /storage/.
 */
export function resolveStorageUrl(url) {
  if (!url) return ''

  if (/^https?:\/\//i.test(url)) {
    try {
      const parsed = new URL(url)
      // Local storage URL: extract just the /storage/... portion so the
      // image loads on the current origin, even if APP_URL differs.
      if (parsed.pathname.includes('/storage/')) {
        return parsed.pathname
      }
      // External URL (S3, Cloudinary, etc.) — use as-is.
      return url
    } catch {
      return url
    }
  }

  if (url.startsWith('/')) return url

  return `/storage/${url}`
}

export const resolveAvatarUrl = resolveStorageUrl
