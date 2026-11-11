// Some deployed translation runtimes return raw strings instead of interpolating
// named parameters. Resolve remaining tokens without treating any value as HTML.
export function formatNamedMessage(translate, key, values) {
  return String(translate(key, values)).replace(/\{(\w+)\}/g, (token, name) =>
    Object.hasOwn(values, name) ? String(values[name]) : token,
  )
}
