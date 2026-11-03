function csrfHeaders(headers) {
  // Laravel checks X-CSRF-TOKEN first. Do not send a stale page token alongside
  // the newer encrypted XSRF cookie after login/session token rotation.
  headers.delete('X-CSRF-TOKEN')
  headers.delete('X-XSRF-TOKEN')
  const cookie = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/)
  if (cookie) {
    headers.set('X-XSRF-TOKEN', decodeURIComponent(cookie[1]))
  } else {
    const token = document.querySelector('meta[name="csrf-token"]')?.content
    if (token) headers.set('X-CSRF-TOKEN', token)
  }
}

export async function fetchConsultationJson(url, options = {}) {
  const method = String(options.method || 'GET').toUpperCase()
  const isWrite = !['GET', 'HEAD', 'OPTIONS'].includes(method)
  const send = () => {
    const headers = new Headers(options.headers)
    headers.set('Accept', 'application/json')
    headers.set('X-Requested-With', 'XMLHttpRequest')
    if (isWrite) csrfHeaders(headers)
    // Apply merged headers last so Content-Type cannot discard CSRF headers.
    return fetch(url, { ...options, method, credentials: 'same-origin', headers })
  }

  let response = await send()
  if (response.status === 419 && isWrite) {
    const refresh = await fetch('/sanctum/csrf-cookie', {
      credentials: 'same-origin', cache: 'no-store',
      headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
    })
    if (refresh.ok) response = await send()
  }

  const data = await response.json().catch(() => null)
  if (!response.ok || response.redirected || data === null) {
    const message = response.status === 419
      ? 'Your session could not be verified. Refresh this page and sign in again.'
      : response.status === 401 || response.redirected
        ? 'Your session has expired. Please sign in again.'
        : data?.message || `Request failed (${response.status})`
    const error = new Error(message)
    error.status = response.status
    throw error
  }
  return data
}
