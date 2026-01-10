import { WebSocketServer, WebSocket } from 'ws'

const port = Number(process.env.SIGNALING_PORT || 3001)
const host = process.env.SIGNALING_HOST || '127.0.0.1'
const wss = new WebSocketServer({ port, host })

/**
 * @typedef {{ peers: Set<import('ws').WebSocket>, roles: Map<string, import('ws').WebSocket> }} Room
 */

/** @type {Map<string, Room>} */
const rooms = new Map()

function safeSend(ws, obj) {
  try {
    if (ws.readyState === WebSocket.OPEN) ws.send(JSON.stringify(obj))
  } catch {
    // ignore
  }
}

function broadcast(roomId, sender, msg) {
  const room = rooms.get(roomId)
  if (!room) return
  for (const ws of room.peers) {
    if (ws !== sender) safeSend(ws, msg)
  }
}

function cleanup(ws) {
  const roomId = ws._roomId
  if (!roomId) return
  const room = rooms.get(roomId)
  if (!room) return

  const role = ws._role
  if (role && room.roles.get(role) === ws) {
    room.roles.delete(role)
  }

  room.peers.delete(ws)
  if (room.peers.size === 0) rooms.delete(roomId)
  else broadcast(roomId, ws, { type: 'peer-left', role })

  ws._roomId = undefined
  ws._role = undefined
}

wss.on('error', (err) => {
  console.error('[signaling] Failed to start WebSocket server:', err?.message || err)
  process.exitCode = 1
})

wss.on('listening', () => {
  console.log(`[signaling] WebSocket server listening on ws://${host}:${port}`)
})

wss.on('connection', (ws) => {
  ws._roomId = undefined
  ws._role = undefined

  ws.on('message', (raw) => {
    let msg
    try {
      msg = JSON.parse(String(raw))
    } catch {
      return safeSend(ws, { type: 'error', message: 'Invalid JSON' })
    }

    if (!msg || typeof msg.type !== 'string') return

    if (msg.type === 'leave') {
      cleanup(ws)
      return safeSend(ws, { type: 'left' })
    }

    if (msg.type === 'join') {
      const roomId = String(msg.roomId || '').trim()
      if (!roomId) return safeSend(ws, { type: 'error', message: 'Missing roomId' })

      const role = String(msg.role || '').trim() || undefined

      // One room per socket.
      cleanup(ws)

      ws._roomId = roomId
      ws._role = role

      if (!rooms.has(roomId)) rooms.set(roomId, { peers: new Set(), roles: new Map() })
      const room = rooms.get(roomId)

      // Enforce at most one socket per role (patient / psychologist).
      // If a user re-joins (refresh, close+open), replace the previous socket to avoid stale peers.
      if (role) {
        const existing = room.roles.get(role)
        if (existing && existing !== ws) {
          try {
            existing.close()
          } catch {
            // ignore
          }
          cleanup(existing)
        }
        room.roles.set(role, ws)
      }

      room.peers.add(ws)

      safeSend(ws, { type: 'joined', roomId, peerCount: room.peers.size })
      broadcast(roomId, ws, { type: 'peer-joined', role })
      return
    }

    const roomId = ws._roomId
    if (!roomId) return safeSend(ws, { type: 'error', message: 'Not joined' })

    // Forward signaling messages to the other peer(s) in the room.
    if (msg.type === 'offer' || msg.type === 'answer' || msg.type === 'ice') {
      return broadcast(roomId, ws, msg)
    }
  })

  ws.on('close', () => cleanup(ws))
  ws.on('error', () => cleanup(ws))
})
