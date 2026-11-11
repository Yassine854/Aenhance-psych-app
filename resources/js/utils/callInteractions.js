const reactionIds = new Set(['thumbs-up', 'happy', 'sad', 'love', 'laugh', 'applause', 'celebrate', 'surprised'])

export { formatNamedMessage as formatCallMessage } from './i18n.js'

// Use the existing room-scoped chat relay so older signaling deployments work too.
// These envelopes are consumed by the call UI before ordinary chat rendering.
export function callInteractionMessage(event, value) {
  return {
    type: 'chat',
    payload: {
      type: 'call-interaction', version: 1, event,
      ...(event === 'hand' ? { raised: value } : { reaction: value }),
    },
  }
}

export function readCallInteraction(message) {
  const payload = message?.payload
  if (message?.type !== 'chat' || payload?.type !== 'call-interaction') return null
  if (payload.version !== 1) return null
  if (payload.event === 'hand' && typeof payload.raised === 'boolean') {
    return { event: 'hand', raised: payload.raised }
  }
  if (payload.event === 'reaction' && reactionIds.has(payload.reaction)) {
    return { event: 'reaction', reaction: payload.reaction }
  }
  return null
}
